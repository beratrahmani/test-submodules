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

namespace SwagBundle\Components;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Model\QueryBuilder;
use SwagBundle\Models\Bundle;

/**
 * The \SwagBundle\Components\BundleComponent component contains all global
 * shopware logic to calculate bundle prices, add bundles to
 * the basket or validate bundles for the current Shopware_Session.
 * The basket component are registered as Shopware resource, which allows
 * you to call the component function via Shopware()->Container()->get('swag_bundle.bundle_component').
 */
interface BundleComponentInterface
{
    /**
     * Constant for the bundle type "normal"
     *
     * The bundle contains a defined price discount and the customer can only buy the complete bundle or not.
     */
    const NORMAL_BUNDLE = 1;

    /**
     * Constant for the bundle type "selectable"
     *
     * The bundle contains no defined price discount but the customer can select the bundle product position.
     */
    const SELECTABLE_BUNDLE = 2;

    /**
     * Constant for the bundle discount type "percentage"
     *
     * If the bundle discount defined as percentage the bundle prices will be calculate by percentage for each customer group.
     */
    const PERCENTAGE_DISCOUNT = 'pro';

    /**
     * Constant for the bundle discount type "absolute"
     *
     * If the bundle discount defined as absolute,
     * the bundle prices are defined absolute per customer group.
     */
    const ABSOLUTE_DISCOUNT = 'abs';

    /**
     * Global interface to get the product bundles.
     *
     * Used to get all defined bundles for the passed product id. This function
     * is used from the Shopware_Plugins_Frontend_SwagBundle_Bootstrap class
     * to get all product bundles on the product detail page in the store front.
     *
     * If the product has bundles in general, but not for the selected variant
     * a boolean is returned which indicates, if this is a bundle product at all
     *
     * @param int    $productId
     * @param string $productNumber
     *
     * @return array|bool
     */
    public function getBundlesForDetailPage($productId, $productNumber);

    /**
     * Helper function to validate all bundles of the basket.
     *
     * @return array|true
     */
    public function validateBundlesInBasket();

    /**
     * This method will be called before sBasket->sGetBasket is called and will update the price of the
     * bundle discount
     */
    public function updateBundleBasketDiscount();

    /**
     * Helper method to remove passed product and bundle row from the basket
     *
     * @param array $params
     */
    public function removeBundleFromBasket(array $params);

    /**
     * Global interface to add a bundle product to the shopware basket.
     * Expects a valid bundle id which defined in the s_products_bundles table.
     * If the bundle contains some configurator products, the product configuration
     * is saved in the shopware session. The selection parameter is used for
     * selectable bundles. This bundles can be configured by the customer.
     * The array contains only the selected bundle products.
     *
     * @param int                   $bundleId
     * @param array|ArrayCollection $selection
     *
     * @return array
     */
    public function addBundleToBasket($bundleId, array $selection = []);

    /**
     * Global interface to check if the passed variant number is already as bundle product position
     * in the basket.
     *
     * @param string $number
     *
     * @return int|null
     */
    public function isVariantAsBundleInBasket($number);

    /**
     * Global interface to check if the passed variant number is already as normal product position
     * in the basket.
     *
     * @param string $number
     *
     * @return int|null
     */
    public function isVariantAsNormalInBasket($number);

    /**
     * Global interface to decrease the bundle stock.
     * Expects a valid bundle id which defined in the s_articles_bundles.
     * If the limited property of the passed bundle is set to true, the quantity will be decrease by one.
     *
     * @param int $bundleId
     */
    public function decreaseBundleStock($bundleId);

    /**
     * Helper method to create the basket query builder
     *
     * @param Bundle $bundle
     *
     * @return QueryBuilder
     */
    public function getBasketBuilder(Bundle $bundle);

    /**
     * Helper method to create the basket attribute query builder
     *
     * @param Bundle $bundle
     *
     * @return QueryBuilder
     */
    public function getBasketAttributeBuilder(Bundle $bundle);

    /**
     * Checks whether a bundle which is added to the basket wasn't deleted/deactivated in the meanwhile.
     */
    public function clearBasketFromDeletedBundles();
}
