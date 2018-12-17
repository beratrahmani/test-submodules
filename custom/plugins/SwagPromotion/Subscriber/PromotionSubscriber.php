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
use Doctrine\DBAL\Query\QueryBuilder;
use Enlight\Event\SubscriberInterface;
use Enlight_Components_Session_Namespace as Session;
use Enlight_Event_EventArgs as EventArgs;
use Enlight_Hook_HookArgs as HookArgs;
use Enlight_Template_Manager as TemplateManager;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware_Components_Config as Config;
use SwagPromotion\Components\Promotion\Selector\Selector;
use SwagPromotion\Components\Services\ProductServiceInterface;

class PromotionSubscriber implements SubscriberInterface
{
    /**
     * Prevent promotions from being inserted recursively.
     *
     * @var bool
     */
    private $ignoreBasketRefresh;

    /**
     * @var string
     */
    private $lastBasketHash;

    /**
     * @var TemplateManager
     */
    private $templateManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var ProductServiceInterface
     */
    private $productService;

    /**
     * @var Selector
     */
    private $promotionSelector;

    /**
     * @param Connection              $connection
     * @param TemplateManager         $templateManager
     * @param Config                  $config
     * @param Session                 $session
     * @param ContextServiceInterface $contextService
     * @param ProductServiceInterface $productService
     * @param Selector                $promotionSelector
     */
    public function __construct(
        Connection $connection,
        TemplateManager $templateManager,
        Config $config,
        Session $session,
        ContextServiceInterface $contextService,
        ProductServiceInterface $productService,
        Selector $promotionSelector
    ) {
        $this->connection = $connection;
        $this->templateManager = $templateManager;
        $this->config = $config;
        $this->session = $session;
        $this->contextService = $contextService;
        $this->productService = $productService;
        $this->promotionSelector = $promotionSelector;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'sBasket::sGetBasket::before' => 'beforeGetBasket',
            'sBasket::sGetBasket::after' => 'afterGetBasket',
            'Shopware_Modules_Basket_GetBasket_FilterSQL' => 'addAttributes',
            'Shopware_Modules_Admin_GetDispatchBasket_QueryBuilder' => 'getDispatchBasketFilterSql',
            'Shopware_Modules_Basket_GetAmountArticles_QueryBuilder' => 'getAmountArticlesFilterSql',
            'Shopware_Modules_Basket_InsertDiscount_FilterSql_BasketAmount' => 'getBasketAmountFilterSql',
        ];
    }

    /**
     * Remove existing promotions
     */
    public function beforeGetBasket()
    {
        if ($this->ignoreBasketRefresh || !$this->basketRefreshNeeded()) {
            return;
        }

        $sql = 'DELETE oba, ob

             FROM s_order_basket ob

             LEFT JOIN s_order_basket_attributes oba
             ON ob.id = oba.basketID

             WHERE ob.sessionID = :sessionId
             AND oba.swag_promotion_id > 0';

        $this->connection->executeQuery(
            $sql,
            [
                'sessionId' => $this->session->get('sessionId'),
            ]
        );
    }

    /**
     * Check and add promotions
     *
     * @param HookArgs $args
     */
    public function afterGetBasket(HookArgs $args)
    {
        if ($this->ignoreBasketRefresh || !$this->basketRefreshNeeded()) {
            return;
        }

        $this->ignoreBasketRefresh = true;

        $appliedPromotions = $this->promotionSelector->apply(
            $args->getReturn(),
            $this->contextService->getShopContext()->getCurrentCustomerGroup()->getId(),
            $this->session->get('sUserId'),
            $this->contextService->getShopContext()->getShop()->getId(),
            array_keys($this->session->get('promotionVouchers')) ?: []
        );

        $basket = $this->populateBasketWithPromotionAttributes($appliedPromotions->basket);

        $args->setReturn($basket);

        if (count($basket) === 0) {
            $this->removePremiumShippingCosts();
        }

        $this->session->offsetSet('appliedPromotions', $appliedPromotions->promotionIds);
        $this->templateManager->assign('availablePromotions', $this->session->get('appliedPromotions'));
        $this->templateManager->assign('promotionsUsedTooOften', $appliedPromotions->promotionsUsedTooOften);
        $this->templateManager->assign('promotionsDoNotMatch', $appliedPromotions->promotionsDoNotMatch);

        $freeGoods = [];
        foreach ($appliedPromotions->freeGoodsArticlesIds as $promotionId => $freeGoodsArticles) {
            $articlesData = $this->productService->getFreeGoods($freeGoodsArticles, $promotionId);
            if (empty($freeGoods)) {
                $freeGoods = $articlesData;
            } else {
                $freeGoods = array_merge($freeGoods, $articlesData);
            }
        }
        $this->templateManager->assign('freeGoods', $freeGoods);

        $this->lastBasketHash = $this->getBasketHash();

        $this->ignoreBasketRefresh = false;
    }

    /**
     * ensure that basket attribute is loaded
     *
     * @param EventArgs $args
     */
    public function addAttributes(EventArgs $args)
    {
        $sql = $args->getReturn();

        $sql = str_replace(
            'ad.stockmin,',
            'ad.stockmin,
            s_order_basket_attributes.swag_is_free_good_by_promotion_id as isFreeGoodByPromotionId,
            s_order_basket_attributes.swag_is_shipping_free_promotion as isShippingFreePromotion,',
            $sql
        );

        $args->setReturn($sql);
    }

    /**
     * @param EventArgs $args
     */
    public function getDispatchBasketFilterSql(\Enlight_Event_EventArgs $args)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $args->get('queryBuilder');
        $amount = $args->get('amount');
        $amount_net = $args->get('amount_net');

        $joinAlias = $this->getJoinAlias($queryBuilder->getQueryPart('join'), 's_order_basket_attributes');

        if ($joinAlias === '') {
            $joinAlias = 'oba';
        }

        $amountSelect = sprintf(
            'SUM(IF(b.modus=0 AND %s.swag_is_free_good_by_promotion_id IS NULL,%s/b.currencyFactor,0)) as amount',
            $joinAlias,
            $amount
        );
        $amountNetSelect = sprintf(
            'SUM(IF(b.modus=0 AND %s.swag_is_free_good_by_promotion_id IS NULL,%s/b.currencyFactor,0)) as amount_net',
            $joinAlias,
            $amount_net
        );

        $queryBuilder
            ->addSelect($amountSelect)
            ->addSelect($amountNetSelect)
            ->leftJoin(
                'b', 's_order_basket_attributes', $joinAlias, 'b.id = ' . $joinAlias . '.basketID'
            );
    }

    /**
     * @param EventArgs $args
     */
    public function getAmountArticlesFilterSql(\Enlight_Event_EventArgs $args)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $args->get('queryBuilder');

        $joinAlias = $this->getJoinAlias($queryBuilder->getQueryPart('join'), 's_order_basket_attributes');

        if ($joinAlias === '') {
            $joinAlias = 'oba';
        }

        $queryBuilder->leftJoin('b', 's_order_basket_attributes', $joinAlias, 'b.id = ' . $joinAlias . '.basketID');
        $queryBuilder->andWhere($joinAlias . '.swag_is_free_good_by_promotion_id IS NULL');
    }

    /**
     * @param EventArgs $args
     */
    public function getBasketAmountFilterSql(\Enlight_Event_EventArgs $args)
    {
        $sql = $args->getReturn();

        $sql = $this->insertBasketAttributeJoin($sql);

        $sql = str_replace(
            'WHERE sessionID = ? AND modus != 4',
            'WHERE b.sessionID = ? AND b.modus != 4 AND ba.swag_is_free_good_by_promotion_id IS NULL',
            $sql
        );

        $args->setReturn($sql);
    }

    /**
     * @param string $sql
     *
     * @return string
     */
    private function insertBasketAttributeJoin($sql)
    {
        $join = 'FROM s_order_basket AS b
                LEFT JOIN s_order_basket_attributes AS ba
                ON b.id = ba.basketID';

        return str_replace(
            'FROM s_order_basket',
            $join,
            $sql
        );
    }

    /**
     * @param array $basket
     *
     * @return array
     */
    private function populateBasketWithPromotionAttributes(array $basket)
    {
        $ids = array_column($basket['content'], 'id');
        if (!$ids) {
            return $basket;
        }

        $questionMarks = implode(', ', array_fill(0, count($ids), '?'));
        $sql = 'SELECT basketId, swag_promotion_id
                FROM s_order_basket_attributes
                WHERE basketId IN (' . $questionMarks . ')';
        $result = $this->connection->executeQuery($sql, $ids)->fetchAll(\PDO::FETCH_UNIQUE);

        $promotionsFromVouchers = array_column(
            $this->session->get('promotionVouchers'),
            'voucherId',
            'promotionId'
        );

        foreach ($basket['content'] as $key => $row) {
            $promotionId = $result[$row['id']]['swag_promotion_id'];
            $promotionVoucherId = array_key_exists(
                $promotionId,
                $promotionsFromVouchers
            ) ? $promotionsFromVouchers[$promotionId] : 0;

            if ($promotionVoucherId === 0) {
                continue;
            }

            $promotionVoucherIds = $this->templateManager->getTemplateVars('promotionVoucherIds');
            if (empty($promotionVoucherIds)) {
                $promotionVoucherIds = [$row['id'] => $promotionVoucherId];
            } else {
                $promotionVoucherIds[$row['id']] = $promotionVoucherId;
            }
            $this->templateManager->assign('promotionVoucherIds', $promotionVoucherIds);
        }

        return $basket;
    }

    /**
     * Check if the basket needs to be recalculated in regards to promotion.
     * This is the case, if the basket is not known, yet (lastBasketHash) or the basket hash changed
     *
     * @return bool
     */
    private function basketRefreshNeeded()
    {
        return !$this->lastBasketHash || $this->lastBasketHash !== $this->getBasketHash();
    }

    /**
     * Calculates a md5 hash over the basket.
     *
     * @return string
     */
    private function getBasketHash()
    {
        $sql = 'SELECT *
                FROM s_order_basket sob
                LEFT JOIN s_order_basket_attributes soba
                  ON soba.basketID = sob.id
                WHERE sob.sessionID = :sessionId';

        $data['basket'] = $this->connection->fetchAll($sql, ['sessionId' => $this->session->get('sessionId')]);
        // subshop switches etc.
        $data['shop'] = $this->contextService->getShopContext();

        return md5(serialize($data));
    }

    /**
     * Helper function to remove premium shipping costs when the basket is empty after deleting a promotion
     */
    private function removePremiumShippingCosts()
    {
        $surcharge_ordernumber = $this->config->get('sPAYMENTSURCHARGEABSOLUTENUMBER', 'PAYMENTSURCHARGEABSOLUTENUMBER');
        $discount_basket_ordernumber = $this->config->get('sDISCOUNTNUMBER', 'DISCOUNT');
        $discount_ordernumber = $this->config->get('sSHIPPINGDISCOUNTNUMBER', 'SHIPPINGDISCOUNT');
        $percent_ordernumber = $this->config->get('sPAYMENTSURCHARGENUMBER', 'PAYMENTSURCHARGE');

        $sql = 'DELETE FROM s_order_basket 
                WHERE sessionID = :sessionId 
                AND modus IN (3,4) 
                AND ordernumber IN (:numbers)';

        $numbers = implode(',', [
            $surcharge_ordernumber,
            $discount_ordernumber,
            $percent_ordernumber,
            $discount_basket_ordernumber,
        ]);

        $this->connection->executeQuery(
            $sql,
            [
                'sessionId' => $this->session->get('sessionId'),
                'numbers' => $numbers,
            ]
        );
    }

    /**
     * @param array[] $joins
     * @param string  $table
     *
     * @return string
     */
    private function getJoinAlias(array $joins, $table)
    {
        if (!array_key_exists('b', $joins)) {
            return '';
        }

        foreach ($joins['b'] as $join) {
            if ($join['joinTable'] === $table) {
                return $join['joinAlias'];
            }
        }

        return '';
    }
}
