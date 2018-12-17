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
use SwagBundle\Models\Bundle;

interface BundleDiscountServiceInterface
{
    /**
     * Global interface to calculate the bundle discount for the passed bundle id.
     * Returns an array with discount data for the different customer groups and
     * gross/net prices.
     *
     * @param Bundle $bundle
     *
     * @return array
     */
    public function getBundleDiscount(Bundle $bundle);

    /**
     * Inserts the bundle discount in the cart. Considers proportional tax calculation
     *
     * @param Bundle          $bundle
     * @param ArrayCollection $selection
     * @param string          $bundleMainProductNumber
     * @param int             $bundleMainProductBasketId
     *
     * @return array
     */
    public function insertBundleDiscountInCart(
        Bundle $bundle,
        ArrayCollection $selection,
        $bundleMainProductNumber,
        $bundleMainProductBasketId
    );
}
