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

return new SwagPromotion\Struct\Promotion([
    'id' => 1,
    'name' => 'Meine neue Promotion',
    'number' => '08154711',
    'rules' => [
            'and' => [
                    'true1' => [
                        ],
                ],
        ],
    'applyRules' => [
            'and' => [
                    'true1' => [
                        ],
                ],
        ],
    'validFrom' => null,
    'validTo' => null,
    'stackMode' => 'global',
    'amount' => 10.0,
    'step' => 1,
    'maxQuantity' => 0,
    'stopProcessing' => false,
    'type' => 'product.freegoods',
    'shippingFree' => false,
    'maxUsage' => 0,
    'voucher' => 0,
    'disallowVouchers' => false,
    'description' => '',
    'detailDescription' => '',
    'exclusive' => false,
    'priority' => 0,
    'freeGoods' => [
            0 => '13',
            1 => '18',
        ],
    'shops' => [
        ],
    'customerGroups' => [
        ],
    'doNotAllowLater' => [
        ],
    'doNotRunAfter' => [
        ],
    'chunkMode' => 'cheapest',
    'attributes' => [
        ],
    'showBadge' => true,
    'badgeText' => 'TEST BADGE',
    'applyRulesFirst' => false,
    'showHintInBasket' => true,
]);
