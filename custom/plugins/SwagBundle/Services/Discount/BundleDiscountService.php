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

namespace SwagBundle\Services\Discount;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Components\Cart\BasketHelperInterface;
use Shopware\Components\Cart\Struct\DiscountContext;
use Shopware\Models\Customer\Group;
use SwagBundle\Components\BundleBasketInterface;
use SwagBundle\Components\BundleComponentInterface;
use SwagBundle\Models\Article as BundleProduct;
use SwagBundle\Models\Bundle;
use SwagBundle\Services\BundleMainProductServiceInterface;
use SwagBundle\Services\Calculation\BundlePriceCalculatorInterface;
use SwagBundle\Services\CustomerGroupServiceInterface;
use SwagBundle\Services\Dependencies\ProviderInterface;
use SwagBundle\Services\Products\ProductPriceServiceInterface;

class BundleDiscountService implements BundleDiscountServiceInterface
{
    /**
     * @var ProviderInterface
     */
    private $dependenciesProvider;

    /**
     * @var CustomerGroupServiceInterface
     */
    private $customerGroupService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var BundlePriceCalculatorInterface
     */
    private $bundlePriceCalculator;

    /**
     * @var BundleMainProductServiceInterface
     */
    private $bundleMainProductService;

    /**
     * @var ProductPriceServiceInterface
     */
    private $productPriceService;

    /**
     * @var \Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @var BasketHelperInterface
     */
    private $bundleBasketHelper;

    /**
     * @param ProviderInterface                    $dependenciesProvider
     * @param CustomerGroupServiceInterface        $customerGroupService
     * @param Connection                           $connection
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param BundlePriceCalculatorInterface       $bundlePriceCalculator
     * @param BundleMainProductServiceInterface    $bundleMainProductService
     * @param ProductPriceServiceInterface         $productPriceService
     * @param \Shopware_Components_Config          $shopwareConfig
     * @param BasketHelperInterface                $bundleBasketHelper
     */
    public function __construct(
        ProviderInterface $dependenciesProvider,
        CustomerGroupServiceInterface $customerGroupService,
        Connection $connection,
        \Shopware_Components_Snippet_Manager $snippetManager,
        BundlePriceCalculatorInterface $bundlePriceCalculator,
        BundleMainProductServiceInterface $bundleMainProductService,
        ProductPriceServiceInterface $productPriceService,
        \Shopware_Components_Config $shopwareConfig,
        BasketHelperInterface $bundleBasketHelper
    ) {
        $this->dependenciesProvider = $dependenciesProvider;
        $this->customerGroupService = $customerGroupService;
        $this->connection = $connection;
        $this->snippetManager = $snippetManager;
        $this->bundlePriceCalculator = $bundlePriceCalculator;
        $this->bundleMainProductService = $bundleMainProductService;
        $this->productPriceService = $productPriceService;
        $this->shopwareConfig = $shopwareConfig;
        $this->bundleBasketHelper = $bundleBasketHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getBundleDiscount(Bundle $bundle)
    {
        $total = $bundle->getTotalPrice();
        $sArticleCoreModule = $this->dependenciesProvider->getArticlesModule();

        $discount = [
            'percentage' => $bundle->getCurrentPrice()->getPercentage(),
            'gross' => $total['gross'] - $bundle->getCurrentPrice()->getGrossPrice(),
            'net' => $total['net'] - $bundle->getCurrentPrice()->getNetPrice(),
        ];

        if ($this->customerGroupService->useNetPriceInBasket()) {
            $discount['gross'] = $discount['net'];
        }

        // Need to fix a floating point issue in the core. Discounts less than 0.00005 will be evaluated as 0
        $discount['net'] = $discount['net'] < 0.00005 ? 0 : $discount['net'];
        $discount['gross'] = $discount['gross'] < 0.00005 ? 0 : $discount['gross'];

        $discount['display'] = $sArticleCoreModule->sFormatPrice($discount['net']);
        $discount['usage'] = $discount['net'];

        if ($bundle->getCurrentPrice()->getCustomerGroup()->getTax()) {
            $discount['display'] = $sArticleCoreModule->sFormatPrice($discount['gross']);
            $discount['usage'] = $discount['gross'];
        }

        return $discount;
    }

    /**
     * {@inheritdoc}
     */
    public function insertBundleDiscountInCart(
        Bundle $bundle,
        ArrayCollection $selection,
        $bundleMainProductNumber,
        $bundleMainProductBasketId
    ) {
        $bundleType = $bundle->getType();
        $bundleDiscountType = $bundle->getDiscountType();
        $useNetPrice = $this->customerGroupService->useNetPriceInBasket();
        $discount = $this->getDiscount($bundle, $selection, $bundleType, $bundleDiscountType, $useNetPrice);

        // Don't add empty bundle discounts
        if ((int) $discount['gross'] === 0) {
            return ['success' => true];
        }

        $sArticleCoreModule = $this->dependenciesProvider->getArticlesModule();
        $session = $this->dependenciesProvider->getSession();

        $shop = $this->dependenciesProvider->getShop();

        $bundleId = $bundle->getId();
        $sessionId = $session->get('sessionId');
        /** @var \Enlight_Components_Snippet_Namespace $namespace */
        $namespace = $this->snippetManager->getNamespace('frontend/checkout/cart_item');
        $discountCartPositionName = $namespace->get('CartItemInfoBundle', 'Bundle discount');
        $bundleOrderNumber = $bundle->getNumber();
        $currencyFactor = $shop->getCurrency()->getFactor();

        if ($this->shopwareConfig->get('proportionalTaxCalculation') && !$session->get('taxFree')) {
            $discountContext = new DiscountContext(
                $sessionId,
                BasketHelperInterface::DISCOUNT_ABSOLUTE,
                $useNetPrice ? $discount['net'] : $discount['gross'],
                $discountCartPositionName,
                $bundleOrderNumber,
                BundleBasketInterface::BUNDLE_DISCOUNT_BASKET_MODE,
                $currencyFactor,
                $useNetPrice
            );

            $discountContext->addAttribute('bundleDiscount', new Attribute([
                'bundleId' => $bundleId,
                'bundleMainProductNumber' => $bundleMainProductNumber,
                'bundleMainProductBasketId' => $bundleMainProductBasketId,
            ]));

            $this->bundleBasketHelper->addProportionalDiscount($discountContext);
        } else {
            $data = [
                'sessionID' => $sessionId,
                'articlename' => $discountCartPositionName,
                'articleID' => 0,
                'ordernumber' => $bundleOrderNumber,
                'shippingfree' => 0,
                'quantity' => 1,
                'price' => $discount['gross'],
                'netprice' => $discount['net'],
                'datum' => 'NOW()',
                'modus' => BundleBasketInterface::BUNDLE_DISCOUNT_BASKET_MODE,
                'tax_rate' => $sArticleCoreModule->getTaxRateByConditions($bundle->getArticle()->getTax()->getId()),
                'currencyFactor' => $currencyFactor,
            ];

            $this->connection->insert('s_order_basket', $data);

            $basketId = $this->connection->lastInsertId('s_order_basket');

            $data = [
                'basketID' => $basketId,
                'bundle_id' => $bundleId,
                'bundle_article_ordernumber' => $bundleMainProductNumber,
                'bundle_package_id' => $bundleMainProductBasketId,
            ];
            $this->connection->insert('s_order_basket_attributes', $data);
        }

        return ['success' => true];
    }

    /**
     * Helper function to calculate the discount value for a selectable bundle
     *
     * @param Bundle          $bundle    has to be a full calculated bundle ($this->getCalculatedBundle)
     * @param ArrayCollection $selection
     *
     * @return array
     */
    private function getDiscountForSelectableBundles(Bundle $bundle, ArrayCollection $selection)
    {
        $customerGroup = $this->customerGroupService->getCurrentCustomerGroup();

        //the discount for selectable bundles has to be calculated
        //by the "getTotalProductPriceForSelectableBundle" function to
        //get the total price for the selected products.
        $bundle->setTotalPrice($this->getTotalProductPriceForSelectableBundle($bundle, $customerGroup, $selection));

        //after the new total price was set, we can calculate the new bundle prices.
        $prices = $this->bundlePriceCalculator->getBundlePrices($bundle);

        //we have to clear the prices before to prevent double price definitions.
        $bundle->getPrices()->clear();
        $bundle->setPrices($prices);

        //now we have to set the current price for the customer group
        $bundle->setCurrentPrice($this->bundlePriceCalculator->getCurrentPrice($bundle, $customerGroup));

        return $this->getBundleDiscount($bundle);
    }

    /**
     * Method to calculate the total product price of the bundle positions
     * for a selectable bundle.
     *
     * @param Bundle          $bundle
     * @param Group           $customerGroup
     * @param ArrayCollection $selection
     *
     * @return array|bool
     */
    private function getTotalProductPriceForSelectableBundle(
        Bundle $bundle,
        Group $customerGroup,
        ArrayCollection $selection
    ) {
        //to calculate the whole bundle price and check if the whole bundle is configured,
        //we have to fake an additional bundle product position with the bundle main product.
        //The bundle main product will not be returned as bundle position.
        $bundleProducts = [];
        $bundleProducts[] = $this->bundleMainProductService->getBundleMainProduct($bundle);
        $bundleProducts = array_merge($bundleProducts, $bundle->getArticles()->getValues());

        $total = [
            'net' => 0,
            'gross' => 0,
        ];

        //iterate all bundle products to get the product data.
        /** @var BundleProduct $bundleProduct */
        foreach ($bundleProducts as $bundleProduct) {
            if (!$bundleProduct instanceof BundleProduct) {
                continue;
            }

            //checks if the current bundle product was selected by the customer
            if ($bundleProduct->getId() > 0
                && $bundle->getType() === BundleComponentInterface::SELECTABLE_BUNDLE
                && !$selection->contains($bundleProduct)
            ) {
                continue;
            }

            //get net and gross price for the selected variant and the current customer group.
            $prices = $this->productPriceService->getProductPrices(
                $bundleProduct->getArticleDetail(),
                $customerGroup,
                $bundleProduct->getQuantity(),
                'EK'
            );

            $total['gross'] += $prices['gross'];
            $total['net'] += $prices['net'];
        }

        return $total;
    }

    /**
     * @param Bundle          $bundle
     * @param ArrayCollection $selection
     * @param string          $bundleType
     * @param string          $bundleDiscountType
     * @param bool            $useNetPrice
     *
     * @return array
     */
    private function getDiscount(
        Bundle $bundle,
        ArrayCollection $selection,
        $bundleType,
        $bundleDiscountType,
        $useNetPrice
    ) {
        $discount = [];
        //we have to check the bundle type.
        if ($bundleType === BundleComponentInterface::NORMAL_BUNDLE) {
            //the discount of normale bundle were already calculated by
            //the getCalculatedBundle function.
            $discount = $bundle->getDiscount();
        } elseif ($bundleType === BundleComponentInterface::SELECTABLE_BUNDLE
            && $bundleDiscountType === BundleComponentInterface::PERCENTAGE_DISCOUNT
        ) {
            $discount = $this->getDiscountForSelectableBundles($bundle, $selection);
        } elseif ($bundleType === BundleComponentInterface::SELECTABLE_BUNDLE
            && $bundleDiscountType === BundleComponentInterface::ABSOLUTE_DISCOUNT
        ) {
            $discount = $bundle->getDiscount();
        }

        /*
         * The discount in the basket is calculated based on the net price and the tax (sGetBasket).
         * The net price of the bundle is calculated as the sum of all products' net prices, so we cannot just
         * add the bundle's default product's tax rate to get the correct absolute price.
         * Instead we calculate the net price from the gross price
         */
        if ($bundleDiscountType !== BundleComponentInterface::PERCENTAGE_DISCOUNT && !$useNetPrice) {
            $tax = $bundle->getArticle()->getTax()->getTax();
            $discount['net'] = $discount['gross'] / ($tax / 100 + 1);
        }

        $discount['net'] = abs($discount['net']) * -1;
        $discount['gross'] = abs($discount['gross']) * -1;

        return $discount;
    }
}
