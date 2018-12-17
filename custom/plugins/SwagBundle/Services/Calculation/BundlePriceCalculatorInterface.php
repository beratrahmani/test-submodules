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

namespace SwagBundle\Services\Calculation;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Models\Customer\Group;
use SwagBundle\Models\Bundle;
use SwagBundle\Models\Price;

interface BundlePriceCalculatorInterface
{
    /**
     * Get the net and gross price for the passed bundle.
     *
     * @param Bundle $bundle
     *
     * @return ArrayCollection|false
     */
    public function getBundlePrices(Bundle $bundle);

    /**
     * Get the price for the passed customer group.
     *
     * @param Bundle     $bundle
     * @param Group|null $customerGroup
     *
     * @return bool|Price
     */
    public function getCurrentPrice(Bundle $bundle, Group $customerGroup = null);
}
