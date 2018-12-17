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

namespace SwagPromotion\Tests\Functional\Controller\Api\Fixtures;

class PostData
{
    public function getPostData()
    {
        return [
            '_dc' => '1490795858552',
            'module' => 'backend',
            'controller' => 'SwagPromotion',
            'action' => 'create',
            'applyRules' => '{"and":{"true0.5629873857242442":[null,null,""]}}',
            'rules' => '{"and":{"true0.5629873857242442":[null,null,""]}}',
            'amount' => 10,
            'shippingFree' => false,
            'type' => 'basket.absolute',
            'stackMode' => 'global',
            'name' => 'Meine neue Promotion',
            'number' => '08154711',
            'active' => true,
            'priority' => 0,
            'description' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.',
            'maxUsage' => 1,
            'stopProcessing' => false,
            'orders' => 0,
            'turnover' => 0,
            'voucherId' => -1,
            'noVouchers' => false,
            'voucherButton' => '',
            'detailDescription' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',
            'exclusive' => false,
            'showBadge' => true,
            'badgeText' => 'Lorem Ipsum',
            '_applyrules' => [],
            'voucher' => [],
            'customerGroups' => [],
            'shops' => [],
            'promotionRules' => [
                [
                    'id' => null,
                    'applyRules' => '{"and":{"true1":[]}}',
                    'rules' => '{"and":{"basketCompareRule0.6230162851519923":["amountGross",">=","60"]}}',
                    '_rules' => [],
                    'customerGroups' => [],
                    'shops' => [],
                    '_applyrules' => [],
                    'doNotRunAfter' => [],
                    'doNotAllowLater' => [],
                ],
            ],
            'doNotRunAfter' => [],
            'doNotAllowLater' => [],
            'freeGoodsArticle' => [],
            'applyRulesFirst' => 0,
        ];
    }
}
