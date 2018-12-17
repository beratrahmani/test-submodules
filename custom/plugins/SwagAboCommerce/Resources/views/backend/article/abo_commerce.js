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

// {block name="backend/article/application"}
    // {$smarty.block.parent}
    // {include file="backend/article/controller/abo_commerce.js"}

    // {include file="backend/article/store/abo_commerce/detail.js"}
    // {include file="backend/article/model/abo_commerce/abo_commerce.js"}
    // {include file="backend/article/model/abo_commerce/price.js"}

    // {include file="backend/article/view/abo_commerce/configuration.js"}
    // {include file="backend/article/view/abo_commerce/tabs/price.js"}
    // {include file="backend/article/view/abo_commerce/tabs/price_grid.js"}
    // {include file="backend/article/view/abo_commerce/tabs/settings.js"}
// {/block}
