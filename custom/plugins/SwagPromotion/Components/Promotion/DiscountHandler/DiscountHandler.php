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

namespace SwagPromotion\Components\Promotion\DiscountHandler;

use SwagPromotion\Components\Promotion\DiscountCommand\Command\DiscountCommand;
use SwagPromotion\Struct\Promotion;

interface DiscountHandler
{
    /**
     * getDiscountCommand should return the amount (gross) of the implementing DiscountHandler.
     *
     * Amount values evaluating to false will result in the promotion not being considered as applied,
     * no discount will be written to false.
     *
     * @param $basket               array   Nested basket array as seen in sGetBasket
     * @param $stackedProducts      array   Array with stacked products as configured in the
     *                                      promotion's step and stackMode settings
     * @param $promotion        Promotion   The promotion object with all configured settings
     *
     * @return DiscountCommand The discount command
     */
    public function getDiscountCommand($basket, $stackedProducts, Promotion $promotion);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function supports($name);
}
