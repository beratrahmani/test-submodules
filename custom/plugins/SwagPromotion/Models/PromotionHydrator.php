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

namespace SwagPromotion\Models;

use SwagPromotion\Components\Promotion\ProductChunker\CheapestProductChunker;
use SwagPromotion\Struct\Promotion as PromotionStruct;

class PromotionHydrator implements Hydrator
{
    /**
     * {@inheritdoc}
     */
    public function hydrate(array $promotions)
    {
        $result = [];
        foreach ($promotions as $promotion) {
            $result[] = new PromotionStruct(
                [
                    'id' => (int) $promotion['id'],
                    'name' => $promotion['name'],
                    'rules' => $this->normalizeRules(json_decode($promotion['rules'], true)),
                    'applyRules' => json_decode($promotion['apply_rules'], true),
                    'validFrom' => $promotion['valid_from'],
                    'validTo' => $promotion['valid_to'],
                    'stackMode' => $promotion['stack_mode'] ?: 'global',
                    'amount' => (float) $promotion['amount'],
                    'step' => (int) $promotion['step'] ?: 1,
                    'maxQuantity' => (int) $promotion['max_quantity'] ?: 0,
                    'stopProcessing' => (bool) $promotion['stop_processing'],
                    'type' => $promotion['type'],
                    'number' => !empty($promotion['number']) ? $promotion['number'] : 'prom-' . $promotion['id'],
                    'exclusive' => (bool) $promotion['exclusive'],
                    'priority' => (int) $promotion['priority'],
                    'shippingFree' => (bool) $promotion['shipping_free'],
                    'maxUsage' => (int) $promotion['max_usage'],
                    'description' => html_entity_decode($promotion['description']),
                    'detailDescription' => html_entity_decode($promotion['detail_description']),
                    'voucher' => (int) $promotion['voucher_id'],
                    'disallowVouchers' => (bool) $promotion['no_vouchers'],
                    'freeGoods' => $promotion['free_goods'] ?: [],
                    'shops' => $promotion['shops'] ?: [],
                    'customerGroups' => $promotion['customer_groups'] ?: [],
                    'doNotAllowLater' => $promotion['do_not_allow_later'] ?: [],
                    'doNotRunAfter' => $promotion['do_not_run_after'] ?: [],
                    // allows you to switch the chunking mode
                    'chunkMode' => !empty($promotion['chunk_mode']) ? $promotion['chunk_mode'] : CheapestProductChunker::CHEAPEST_PRODUCT_CHUNKER_NAME,
                    'showBadge' => (bool) $promotion['show_badge'],
                    'badgeText' => $promotion['badge_text'],
                    'applyRulesFirst' => (bool) $promotion['apply_rules_first'],
                    'showHintInBasket' => (bool) $promotion['show_hint_in_basket'],
                ]
            );
        }

        return $result;
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    private function normalizeRules(array $rules)
    {
        foreach ($rules as $k => $conditions) {
            foreach ($conditions as $key => $value) {
                if ($key === '' || empty($key)) {
                    unset($conditions[$key]);
                }
            }
            $rules[$k] = $conditions;
        }

        return $rules;
    }
}
