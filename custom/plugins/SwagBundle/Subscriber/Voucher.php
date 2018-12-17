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

namespace SwagBundle\Subscriber;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Enlight\Event\SubscriberInterface;
use Enlight_Hook_HookArgs as HookArgs;
use Enlight_Template_Manager as TemplateManager;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Shop\DetachedShop;
use SwagBundle\Components\BundleBasketInterface;
use SwagBundle\Services\Dependencies\ProviderInterface;
use SwagBundle\Services\VoucherServiceInterface;

class Voucher implements SubscriberInterface
{
    /**
     * @var VoucherServiceInterface
     */
    private $voucherService;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var ProviderInterface
     */
    private $dependenciesProvider;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TemplateManager
     */
    private $templateManager;

    /**
     * @var \Enlight_Controller_Front
     */
    private $front;

    /**
     * @param VoucherServiceInterface   $voucherService
     * @param ConfigReader              $configReader
     * @param string                    $pluginName
     * @param ProviderInterface         $dependenciesProvider
     * @param Connection                $connection
     * @param TemplateManager           $templateManager
     * @param \Enlight_Controller_Front $front
     */
    public function __construct(
        VoucherServiceInterface $voucherService,
        ConfigReader $configReader,
        $pluginName,
        ProviderInterface $dependenciesProvider,
        Connection $connection,
        TemplateManager $templateManager,
        \Enlight_Controller_Front $front
    ) {
        $this->voucherService = $voucherService;
        $this->configReader = $configReader;
        $this->pluginName = $pluginName;
        $this->dependenciesProvider = $dependenciesProvider;
        $this->connection = $connection;
        $this->templateManager = $templateManager;
        $this->front = $front;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'sBasket::sGetAmountRestrictedArticles::after' => 'onGetAmountRestrictedProducts',
            'sBasket::sGetAmountArticles::after' => 'onGetAmountProducts',
            'sBasket::sAddVoucher::before' => 'afterAddVoucher',
        ];
    }

    /**
     * Get basket summary for restricted bundle product and suppliers and subtract from basket price.
     *
     * @param HookArgs $args
     *
     * @return array
     */
    public function onGetAmountRestrictedProducts(HookArgs $args)
    {
        $totalAmount = $args->getReturn();
        if ($this->dependenciesProvider->getSession() === null) {
            return $totalAmount;
        }

        $products = $args->get('articles');
        $supplier = $args->get('supplier');
        $basketSum = $totalAmount['totalAmount'];
        $pluginConfig = $this->configReader->getByPluginName($this->pluginName, $this->dependenciesProvider->getShop());
        $hasPercentalVoucher = $this->voucherService->isPercentalVoucherInBasket();

        $excludeVoucher = $pluginConfig['SwagBundleExcludeVoucher'];
        if ($excludeVoucher && $hasPercentalVoucher) {
            $query = $this->getBundleProductsQueryBuilder();
            $query->from('s_articles')
                ->andWhere('s_order_basket.articleID = s_articles.id');

            if (is_array($products) && !empty($products)) {
                $query->andWhere('s_order_basket.ordernumber IN (:productIds)')
                    ->setParameter('productIds', $products, Connection::PARAM_INT_ARRAY);
            }
            if (!empty($supplier)) {
                $query->andWhere('s_articles.supplierID = :supplierId')
                    ->setParameter('supplierId', $supplier);
            }

            $bundleSum = $query->execute()->fetchColumn();
            $basketSum -= $bundleSum;
        } elseif ($pluginConfig['SwagBundleSubtractBundle']) {
            $bundleDiscount = $this->calculateBundleDiscount();
            $basketSum += $bundleDiscount;
        }

        return ['totalAmount' => $basketSum];
    }

    /**
     * Get basket summary for bundle items and subtract from basket price.
     *
     * @param HookArgs $args
     *
     * @return array
     */
    public function onGetAmountProducts(HookArgs $args)
    {
        $totalAmount = $args->getReturn();
        if ($this->dependenciesProvider->getSession() === null) {
            return $totalAmount;
        }

        $basketSum = $totalAmount['totalAmount'];
        $pluginConfig = $this->configReader->getByPluginName($this->pluginName, $this->dependenciesProvider->getShop());
        $hasPercentalVoucher = $this->voucherService->isPercentalVoucherInBasket();

        $excludeVoucher = $pluginConfig['SwagBundleExcludeVoucher'];
        if ($excludeVoucher && $hasPercentalVoucher) {
            $query = $this->getBundleProductsQueryBuilder();

            $bundleSum = $query->execute()->fetchColumn();
            $basketSum -= $bundleSum;
        } elseif ($pluginConfig['SwagBundleSubtractBundle']) {
            $bundleDiscount = $this->calculateBundleDiscount();
            $basketSum += $bundleDiscount;
        }

        // Display custom error instead of minimum order
        if ($excludeVoucher && $this->checkMinOrder($basketSum)) {
            $this->templateManager->assign('sVoucherValidation', true);
        }

        return ['totalAmount' => $basketSum];
    }

    /**
     * @param HookArgs $args
     */
    public function afterAddVoucher(HookArgs $args)
    {
        $voucherCode = $this->front->Request()->getParam('sVoucher');
        if ($voucherCode === null) {
            $this->front->Request()->setParam('sVoucher', $args->get('voucherCode'));
        }
    }

    /**
     * Check if added voucher minimum charge is bigger then basket sum.
     *
     * @param int $totalAmount
     *
     * @return bool
     */
    private function checkMinOrder($totalAmount = 0)
    {
        /** @var DetachedShop $shop */
        $shop = $this->dependenciesProvider->getShop();

        if ($shop === null) {
            return false;
        }

        /** @var array $voucherDetails */
        $voucherDetails = $this->voucherService->getVoucherDetails();
        if (!$voucherDetails) {
            return false;
        }

        $factor = 1;
        if (!$voucherDetails['percental'] && $shop->getCurrency()->getFactor()) {
            $factor = $shop->getCurrency()->getFactor();
        }

        return ($totalAmount / $factor) < $voucherDetails['minimumcharge'];
    }

    /**
     * @return bool|string
     */
    private function calculateBundleDiscount()
    {
        return $this->connection->createQueryBuilder()
            ->select('SUM(quantity*(floor(price * 100 + .55)/100)) AS totalAmount')
            ->from('s_order_basket')
            ->from('s_order_basket_attributes')
            ->andWhere('basketID = s_order_basket.id')
            ->andWhere('sessionID = :sessionId')
            ->andWhere('bundle_id IS NOT NULL')
            ->andWhere('modus = :mode')
            ->groupBy('sessionID')
            ->setParameter('sessionId', $this->dependenciesProvider->getSession()->get('sessionId'))
            ->setParameter('mode', BundleBasketInterface::BUNDLE_DISCOUNT_BASKET_MODE)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return QueryBuilder
     */
    private function getBundleProductsQueryBuilder()
    {
        return $this->connection->createQueryBuilder()
            ->select('SUM(quantity*(floor(price * 100 + .55)/100)) AS totalAmount')
            ->from('s_order_basket')
            ->from('s_order_basket_attributes')
            ->andWhere('s_order_basket.sessionID = :sessionId')
            ->andWhere('s_order_basket.modus = 0')
            ->andWhere('s_order_basket_attributes.basketID = s_order_basket.id')
            ->andWhere('s_order_basket_attributes.bundle_id IS NOT NULL')
            ->groupBy('sessionID')
            ->setParameter(':sessionId', $this->dependenciesProvider->getSession()->get('sessionId'));
    }
}
