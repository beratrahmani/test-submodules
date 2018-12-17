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

namespace SwagPromotion\Tests\Helper;

use SwagPromotion\Struct\Promotion;

class PromotionFactory
{
    private static $id = 1;

    /**
     * @param array $data
     *
     * @return Promotion
     */
    public static function create(array $data)
    {
        $id = isset($data['id']) ? $data['id'] : ++self::$id;
        $default = [
            'id' => $id,
            'name' => 'Promotion name ' . $id,
            'rules' => ['and' => ['true' => []]],
            'applyRules' => ['and' => ['true' => []]],
            'validFrom' => null,
            'validTo' => null,
            'stackMode' => 'global',
            'amount' => 10,
            'step' => 1,
            'maxQuantity' => 0,
            'stopProcessing' => false,
            'type' => 'basket.absolute',
            'number' => 'number' . $id,
            'exclusive' => 0,
            'priority' => 0,
            'shippingFree' => false,
            'maxUsage' => 0,
            'description' => 'description ' . $id,
            'detailDescription' => 'detailDescription' . $id,
            'voucher' => null,
            'disallowVouchers' => false,
            'freeGoods' => null,
            'shops' => [1],
            'customerGroups' => [1],
            'doNotAllowLater' => [],
            'doNotRunAfter' => [],
            'chunkMode' => 'cheapest',
            'showBadge' => true,
            'badgeText' => null,
            'applyRulesFirst' => false,
            'showHintInBasket' => true,
        ];

        return new Promotion(array_merge($default, $data));
    }
}
