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

namespace SwagLiveShopping\Components;

use SwagLiveShopping\Models\LiveShopping as LiveShoppingModel;

/**
 * @deprecated since version 3.2.0, to be removed in 4.0.0
 * Use the SwagLiveShopping\Components\PriceService
 * Id: swag_liveshopping.price_service
 */
interface PriceForDateServiceInterface
{
    /**
     * Calculates the LiveShopping price for the given LiveShopping and date
     *
     * @param LiveShoppingModel $liveShoppingModel
     * @param \DateTime         $date
     *
     * @return float|false
     */
    public function getLiveShoppingPriceForDate(LiveShoppingModel $liveShoppingModel, \DateTime $date);
}
