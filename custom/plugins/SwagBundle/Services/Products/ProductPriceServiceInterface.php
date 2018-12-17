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

namespace SwagBundle\Services\Products;

use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Group;

interface ProductPriceServiceInterface
{
    /**
     * helper to get the gross and net price for a single product variant and customer group.
     *
     * @param Detail      $productVariant
     * @param Group       $customerGroup
     * @param int         $quantity
     * @param string|null $fallback
     *
     * @return array|bool
     */
    public function getProductPrices(Detail $productVariant, Group $customerGroup, $quantity, $fallback = null);

    /**
     * Method to get the base price calculation of a single product variant.
     *
     * @param Detail $variant
     *
     * @return array
     */
    public function getBasePriceOfVariant(Detail $variant);
}
