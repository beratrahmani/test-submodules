<?php
/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagPromotion\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Components_Session_Namespace as Session;
use Enlight_Event_EventArgs;
use Enlight_Template_Manager as Template;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPromotion\Models\Repository\Repository;
use SwagPromotion\Struct\Promotion;

class VoucherSubscriber implements SubscriberInterface
{
    // notifyUntil: stop
    const STOP_PROCESSING = true;

    // notifyUntil: continue
    const ALLOW_PROCESSING = null;

    /**
     * @var
     */
    private $pluginBasePath;

    /**
     * @var Template
     */
    private $templateManager;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * VoucherSubscriber constructor.
     *
     * @param string                  $pluginBasePath
     * @param Template                $templateManager
     * @param Session                 $session
     * @param Repository              $repository
     * @param ContextServiceInterface $contextService
     * @param Connection              $connection
     */
    public function __construct(
        $pluginBasePath,
        Template $templateManager,
        Session $session,
        Repository $repository,
        ContextServiceInterface $contextService,
        Connection $connection
    ) {
        $this->pluginBasePath = $pluginBasePath;
        $this->templateManager = $templateManager;
        $this->session = $session;
        $this->repository = $repository;
        $this->contextService = $contextService;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Basket_AddVoucher_Start' => 'onAddVoucher',
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     *
     * @return bool
     */
    public function onAddVoucher(Enlight_Event_EventArgs $args)
    {
        $code = $args->get('code');

        $this->templateManager->addTemplateDir($this->pluginBasePath . '/Resources/views');

        if (!$code) {
            return self::ALLOW_PROCESSING;
        }

        $appliedPromotions = $this->session->offsetGet('appliedPromotions');

        // Convert from possible null to empty array
        if (!$appliedPromotions) {
            $appliedPromotions = [];
        }

        $promotionsForVoucher = $this->getPromotionsForVoucher($code);

        // Must be a real voucher, no promotion found for this voucher
        if (!$promotionsForVoucher) {
            return $this->handleVoucher($appliedPromotions);
        }

        return $this->handlePromotion($appliedPromotions, $promotionsForVoucher);
    }

    /**
     * Get promotions and vouchers that might belong together
     *
     * @param string $code
     *
     * @return array
     */
    private function getPromotionsForVoucher($code)
    {
        // the union is used as we want to look up vouchers by
        // * general code (mode=0) as well as by
        // * individual code (mode=1)
        $sql = 'SELECT promotions.id AS promotionId, vouchers.id AS voucherId, vouchers.vouchercode AS code, 0 AS mode
                FROM s_emarketing_vouchers vouchers

                INNER JOIN s_plugin_promotion promotions
                  ON promotions.voucher_id = vouchers.id
                WHERE vouchercode = :code
                  AND vouchers.modus = 0

                UNION ALL

                SELECT promotions.id AS promotionId, voucherID AS voucherId, codes.code, 1 AS mode
                FROM s_emarketing_voucher_codes codes
                INNER JOIN s_plugin_promotion promotions
                  ON promotions.voucher_id = codes.voucherID
                WHERE code = :code
                  AND cashed = 0';

        $result = $this->connection->fetchAll($sql, ['code' => $code]);

        $vouchers = [];
        foreach ($result as $voucher) {
            $vouchers[$voucher['promotionId']] = $voucher;
        }

        return $vouchers;
    }

    /**
     * Handles the logic to allow or disallow the newly added voucher
     *
     * @param array $appliedPromotions
     *
     * @return bool|null
     */
    private function handleVoucher(array $appliedPromotions)
    {
        // Checks if vouchers are allowed by the already applied promotions in the basket
        if (!$this->areVouchersAllowed($appliedPromotions)) {
            $this->templateManager->assign('voucherNotCombined', true);

            return self::STOP_PROCESSING;
        }

        $promotionVoucherIds = array_column($this->session->get('promotionVouchers', []), 'promotionId');

        if (!$promotionVoucherIds) {
            $promotionVoucherIds = [];
        }

        // Checks if vouchers are allowed by the available but not yet applied promotions in the basket
        if (!$this->areVouchersAllowed($promotionVoucherIds)) {
            $this->templateManager->assign('voucherNotCombined', true);

            return self::STOP_PROCESSING;
        }

        return self::ALLOW_PROCESSING;
    }

    /**
     * Returns true, if all promotions allow vouchers.
     *
     * @param $appliedPromotions
     *
     * @return mixed
     */
    private function areVouchersAllowed($appliedPromotions)
    {
        $promotions = $this->repository->getActivePromotions(
            $this->contextService->getShopContext()->getCurrentCustomerGroup()->getId(),
            $this->contextService->getShopContext()->getShop()->getId(),
            $this->getVoucherIdsForPromotionIds($appliedPromotions)
        );

        $appliedPromotionsWithVoucherExclude = array_filter(
            $promotions,
            function (Promotion $promotion) use ($appliedPromotions) {
                return in_array($promotion->id, $appliedPromotions) && $promotion->disallowVouchers;
            }
        );

        return empty($appliedPromotionsWithVoucherExclude);
    }

    /**
     * Returns the voucher-ids for the currently active promotions.
     *
     * @param array $promotionIds
     *
     * @return array
     */
    private function getVoucherIdsForPromotionIds(array $promotionIds)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $voucherIds = $queryBuilder->select('promo.voucher_id')
            ->from('s_plugin_promotion', 'promo')
            ->where('promo.id IN (:ids)')
            ->setParameter(':ids', $promotionIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        return $voucherIds;
    }

    /**
     * Handles the logic when a new promotion is added
     *
     * @param array $appliedPromotions
     * @param array $promotionsForVoucher
     *
     * @return bool
     */
    private function handlePromotion(array $appliedPromotions, array $promotionsForVoucher)
    {
        if (!$this->areVouchersAllowed($appliedPromotions)) {
            $this->templateManager->assign('voucherNotCombined', true);

            return self::STOP_PROCESSING;
        }

        $promotionVoucherIds = array_column($this->session->get('promotionVouchers'), 'promotionId');

        if (!$promotionVoucherIds) {
            $promotionVoucherIds = [];
        }

        // Checks if vouchers are allowed by the available but not yet applied promotions in the basket
        if (!$this->areVouchersAllowed($promotionVoucherIds)) {
            $this->templateManager->assign('voucherNotCombined', true);

            return self::STOP_PROCESSING;
        }

        $voucherIds = array_column($promotionsForVoucher, 'voucherId');
        $promotionIds = array_column($promotionsForVoucher, 'promotionId');

        foreach ($promotionIds as $index => $promotionId) {
            if (!$this->shouldAllowNewPromotion($promotionId)) {
                unset($promotionIds[$index]);
            }
        }

        if (!$promotionIds) {
            $this->templateManager->assign('voucherNotCombined', true);

            return self::STOP_PROCESSING;
        }

        $promotions = $this->repository->getActivePromotions(
            $this->contextService->getShopContext()->getCurrentCustomerGroup()->getId(),
            $this->contextService->getShopContext()->getShop()->getId(),
            $voucherIds
        );

        // Filter the promotions that belong to the voucher
        $promotionAvailable = array_filter(
            $promotions,
            function ($promotion) use ($promotionIds) {
                return in_array($promotion->id, $promotionIds);
            }
        );

        // If no promotions remain, we might have an expired promotion
        if (empty($promotionAvailable)) {
            $this->templateManager->assign('voucherExpired', true);

            return self::STOP_PROCESSING;
        }

        // as the promotions are ordered by priority, the first promotion is the one to go for
        $mainPromotion = array_shift($promotionAvailable);

        $result = $promotionsForVoucher[$mainPromotion->id];

        // Mark the promotion / voucher as active in the session
        $this->session->promotionVouchers[$result['voucherId']] = $result;

        $this->templateManager->assign('voucherPromotionId', $result['promotionId']);

        // stop shopware from running its voucher system
        return self::STOP_PROCESSING;
    }

    /**
     * Checks if a promotion should be allowed due to the basket and the promotions settings
     *
     * @param string $promotionId
     *
     * @return bool
     */
    private function shouldAllowNewPromotion($promotionId)
    {
        if (!$this->areVouchersAllowed([$promotionId]) && $this->isVoucherInBasket()) {
            return false;
        }

        return true;
    }

    /**
     * Checks if a voucher or a promotion is already in the basket.
     *
     * @return bool
     */
    private function isVoucherInBasket()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $vouchersInBasket = $queryBuilder->select('basket.id')
            ->from('s_order_basket', 'basket')
            ->leftJoin('basket', 's_order_basket_attributes', 'basketAttr', 'basketAttr.basketID = basket.id')
            ->where('basket.sessionID = :sessionId')
            ->andWhere('(basket.modus = 2 OR (basket.modus = 4 AND basketAttr.swag_promotion_id IS NOT NULL))')
            ->setParameter(':sessionId', $this->session->get('sessionId'))
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        return !empty($vouchersInBasket);
    }
}
