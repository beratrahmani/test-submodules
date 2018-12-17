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

namespace SwagBundle\Services;

use Shopware\Models\Order\Basket;
use SwagBundle\Models\Bundle;

interface FullBundleServiceInterface
{
    /**
     * Global interface to get the full data for a single bundle.
     * The function returns the standard values for a bundle, with additional information
     * like the discount value, product images, product configuration, etc.
     *
     * @param Bundle      $bundle
     * @param string      $productNumber
     * @param bool        $isBasket
     * @param Basket|null $basketItem
     *
     * @return Bundle|array
     */
    public function getCalculatedBundle(
        Bundle $bundle,
        $productNumber = '',
        $isBasket = false,
        Basket $basketItem = null
    );
}
