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
use Doctrine\ORM\AbstractQuery;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Attribute\OrderBasket;
use SwagBundle\Components\BundleBasketInterface;
use SwagBundle\Components\BundleComponentInterface;
use SwagBundle\Models\Bundle;
use SwagBundle\Services\Dependencies\ProviderInterface;
use SwagBundle\Services\VoucherServiceInterface;

class Checkout implements SubscriberInterface
{
    /**
     * @var BundleBasketInterface
     */
    private $bundleBasket;

    /**
     * @var BundleComponentInterface
     */
    private $bundleComponent;

    /**
     * @var ModelManager
     */
    private $modelManager;

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
     * @var VoucherServiceInterface
     */
    private $voucherService;

    /**
     * @var \Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param BundleBasketInterface       $bundleBasket
     * @param BundleComponentInterface    $bundleComponent
     * @param ModelManager                $modelManager
     * @param ConfigReader                $configReader
     * @param string                      $pluginName
     * @param ProviderInterface           $dependenciesProvider
     * @param \Shopware_Components_Config $shopwareConfig
     * @param VoucherServiceInterface     $voucherService
     * @param Connection                  $connection
     */
    public function __construct(
        BundleBasketInterface $bundleBasket,
        BundleComponentInterface $bundleComponent,
        ModelManager $modelManager,
        ConfigReader $configReader,
        $pluginName,
        ProviderInterface $dependenciesProvider,
        \Shopware_Components_Config $shopwareConfig,
        VoucherServiceInterface $voucherService,
        Connection $connection
    ) {
        $this->bundleBasket = $bundleBasket;
        $this->bundleComponent = $bundleComponent;
        $this->modelManager = $modelManager;
        $this->configReader = $configReader;
        $this->pluginName = $pluginName;
        $this->dependenciesProvider = $dependenciesProvider;
        $this->shopwareConfig = $shopwareConfig;
        $this->voucherService = $voucherService;
        $this->connection = $connection;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onCheckoutPostDispatch',
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout' => 'onCheckoutPreDispatch',
            'Shopware_Controllers_Frontend_Checkout::getBasket::after' => 'afterGetBasket',
            'Shopware_Controllers_Frontend_Checkout::saveOrder::after' => 'onSaveOrder',
            'sBasket::sGetBasket::before' => 'onGetBasket',
            'Shopware_Modules_Basket_AddArticle_Start' => 'onAddProductStart',
            'sBasket::sDeleteArticle::before' => 'onDeleteArticle',
            'Shopware_Modules_Basket_GetBasket_FilterSQL' => 'addBundleIdToBasket',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function addBundleIdToBasket(\Enlight_Event_EventArgs $args)
    {
        $bundleAttributesSelectString = sprintf(
            's_order_basket_attributes.attribute6 as ob_attr6, %s, %s, %s',
            's_order_basket_attributes.bundle_id AS bundleId',
            's_order_basket_attributes.bundle_article_ordernumber AS bundleArticleOrdernumber',
            's_order_basket_attributes.bundle_package_id AS bundlePackageId'
        );

        $sql = str_replace(
            's_order_basket_attributes.attribute6 as ob_attr6',
            $bundleAttributesSelectString,
            $args->getReturn()
        );

        $args->setReturn($sql);
    }

    /**
     * Enlight event listener function.
     * Fired on the checkout section in the shop frontend.
     * Used to extend the cart_item.tpl template.
     *
     * @param \Enlight_Controller_ActionEventArgs $arguments
     *
     * @throws \Exception
     */
    public function onCheckoutPostDispatch(\Enlight_Controller_ActionEventArgs $arguments)
    {
        /** @var \Enlight_Controller_Action $subject */
        $subject = $arguments->getSubject();

        /** @var \Enlight_Controller_Request_Request $request */
        $request = $subject->Request();

        /** @var \Enlight_View_Default $view */
        $view = $subject->View();

        if ($request->has('sBundleValidation')) {
            $view->assign('sBundleValidation', $request->getParam('sBundleValidation'));
        }
    }

    /**
     * Enlight event listener function.
     * Fired when the customer enters the checkout section.
     *
     * @param \Enlight_Controller_ActionEventArgs $arguments
     */
    public function onCheckoutPreDispatch(\Enlight_Controller_ActionEventArgs $arguments)
    {
        /** @var \Enlight_Controller_Action $subject */
        $subject = $arguments->getSubject();

        /** @var \Enlight_Controller_Request_Request $request */
        $request = $subject->Request();

        /** @var \Enlight_View_Default $view */
        $view = $subject->View();

        $validation = $this->bundleComponent->validateBundlesInBasket();
        $this->bundleComponent->clearBasketFromDeletedBundles();

        if ($validation === true) {
            return;
        }

        $view->assign('sBundleValidation', $validation);

        if (in_array($request->getActionName(), ['finish', 'payment'], true)) {
            $subject->forward('confirm', 'checkout', 'frontend', ['sBundleValidation' => $validation]);
        }
    }

    /**
     * Hook for the Shopware_Controllers_Frontend_Checkout saveOrder method.
     * The hook is fired after the saveOrder function is done.
     *
     * @param \Enlight_Hook_HookArgs $arguments
     *
     * @throws \Exception
     */
    public function onSaveOrder(\Enlight_Hook_HookArgs $arguments)
    {
        $orderNumber = $arguments->getReturn();

        if ($orderNumber === '') {
            return;
        }

        $query = $this->connection->createQueryBuilder();
        $bundleIds = $query->select('bundle_id')
            ->from('s_order_details_attributes', 'orderDetailsAttributes')
            ->innerJoin(
                'orderDetailsAttributes',
                's_order_details',
                'orderDetails',
                'orderDetailsAttributes.detailID = orderDetails.id'
            )
            ->where('ordernumber = :orderNumber')
            ->andWhere('modus = :mode')
            ->setParameter('orderNumber', $orderNumber)
            ->setParameter('mode', BundleBasketInterface::BUNDLE_DISCOUNT_BASKET_MODE)
            ->execute()->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($bundleIds as $bundleId) {
            $this->bundleComponent->decreaseBundleStock($bundleId);
        }
    }

    public function onGetBasket()
    {
        $this->bundleComponent->updateBundleBasketDiscount();
    }

    /**
     * Listener function for the Shopware_Modules_Basket_AddArticle_Start event
     *
     * @param \Enlight_Event_EventArgs $arguments
     *
     * @return true|null
     */
    public function onAddProductStart(\Enlight_Event_EventArgs $arguments)
    {
        $orderNumber = $arguments->get('id');
        $quantity = $arguments->get('quantity');

        $normalPosition = $this->bundleComponent->isVariantAsNormalInBasket($orderNumber);
        $bundlePosition = $this->bundleComponent->isVariantAsBundleInBasket($orderNumber);

        if ($bundlePosition !== null && $normalPosition === null) {
            //bundle position exist? and no normal position exist? Force new basket position
            $this->bundleBasket->addProduct($orderNumber, $quantity, ['forceNewPosition' => true]);

            return true;
        }

        if ($bundlePosition !== null && $normalPosition !== null) {
            //bundle position exist? and a normal position exist? Update the normal position
            $this->bundleBasket->addProduct($orderNumber, $quantity, ['updatePosition' => $normalPosition]);

            return true;
        }

        return null;
    }

    /**
     * Enlight event listener function of the sBasket::sDeleteArticle function.
     * Fired when the customer removes a product position or the already added
     * position fulfills no more the conditions to stay in the basket.
     *
     * @param \Enlight_Hook_HookArgs $arguments
     */
    public function onDeleteArticle(\Enlight_Hook_HookArgs $arguments)
    {
        $id = (int) $arguments->get('id');

        if ($id === 0) {
            return;
        }

        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['attribute'])
            ->from(OrderBasket::class, 'attribute')
            ->where('attribute.orderBasketId = :basketId')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->setParameter('basketId', $id);

        $basketRowAttributes = $builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if ($basketRowAttributes === null) {
            return;
        }

        if ($basketRowAttributes['bundleId'] > 0) {
            /** @var Bundle $bundle */
            $bundle = $this->modelManager->find(Bundle::class, $basketRowAttributes['bundleId']);
            $this->bundleComponent->removeBundleFromBasket(
                [
                    'bundle' => $bundle,
                    'productNumber' => $basketRowAttributes['bundleArticleOrdernumber'],
                    'bundlePackageId' => $basketRowAttributes['bundlePackageId'],
                ]
            );
        }
    }

    /**
     * Recalculate basket vouchers and basket summary prices and taxes when bundle items are found
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function afterGetBasket(\Enlight_Event_EventArgs $args)
    {
        $pluginConfig = $this->configReader->getByPluginName($this->pluginName, $this->dependenciesProvider->getShop());

        if (!$pluginConfig['SwagBundleExcludeVoucher']) {
            return;
        }

        $havePercentVoucher = false;
        $haveBundleItems = false;
        $amount = 0;
        $amountNet = 0;
        $bundleDiscount = 0;
        $result = [];

        $basket = $args->getReturn();

        if (!empty($basket['sShippingcostsTax'])) {
            $basket['sShippingcostsTax'] = $this->formatNumber($basket['sShippingcostsTax']);

            $shippingCosts = $basket['sShippingcostsWithTax'] - $basket['sShippingcostsNet'];

            if (!empty($shippingCosts)) {
                $result[$basket['sShippingcostsTax']] = $shippingCosts;
            }
        } elseif ($basket['sShippingcostsWithTax']) {
            $shippingCosts = $basket['sShippingcostsWithTax'] - $basket['sShippingcostsNet'];

            if (!empty($shippingCosts)) {
                $taxShipping = $this->formatNumber($this->shopwareConfig->get('sTAXSHIPPING'));
                $result[$taxShipping] = $shippingCosts;
            }
        }

        foreach ($basket['content'] as $basketItem) {
            if ($basketItem['bundleId'] > 0) {
                $haveBundleItems = true;
            }

            if (empty($basketItem['tax_rate']) || empty($basketItem['tax'])) {
                $basketItem['tax_rate'] = 0;
                $basketItem['tax'] = 0;
            }

            $taxKey = $this->formatNumber($basketItem['tax_rate']);

            // Exclude vouchers
            if ((int) $basketItem['modus'] !== 2) {
                $amount += $this->priceToFloat($basketItem['amount']);
                $amountNet += $this->priceToFloat($basketItem['amountnet']);

                $result[$taxKey] += $this->priceToFloat($basketItem['tax']);
            }

            // Recalculate percental voucher discount for non bundle items
            if ((int) $basketItem['modus'] === 2) {
                $percentVoucher = $this->voucherService->isCodeFromPercentalVoucher($basketItem['ordernumber']);

                if ($percentVoucher && $pluginConfig['SwagBundleSubtractBundle']) {
                    $amount += $this->priceToFloat($basketItem['amount']);
                    $amountNet += $this->priceToFloat($basketItem['amountnet']);

                    $havePercentVoucher = true;
                    $result[$taxKey] += $this->priceToFloat($basketItem['tax']);
                }
            }

            if ((int) $basketItem['modus'] === BundleBasketInterface::BUNDLE_DISCOUNT_BASKET_MODE) {
                $bundleDiscount += $this->priceToFloat($basketItem['amount']);
            }
        }
        // Remove tax-free voucher
        unset($result['0.00']);
        ksort($result, SORT_NUMERIC);

        if (!$haveBundleItems || !$havePercentVoucher) {
            return;
        }

        $totalAmount = $amount + $basket['sShippingcostsWithTax'];
        $amountNet += $basket['sShippingcostsNet'];

        $basket['Amount'] = $amount;
        $basket['sAmount'] = $totalAmount;
        $basket['AmountNet'] = $amountNet;
        $basket['sAmountNet'] = $amountNet;
        $basket['sTaxRates'] = $result;
        $basket['AmountNetNumeric'] = $amountNet;
        $basket['AmountWithTaxNumeric'] = $totalAmount;

        $args->setReturn($basket);
    }

    /**
     * @param string $number
     *
     * @return string
     */
    private function formatNumber($number)
    {
        return number_format((float) $number, 2);
    }

    /**
     * @param string $price
     *
     * @return float
     */
    private function priceToFloat($price)
    {
        return (float) str_replace(',', '.', $price);
    }
}
