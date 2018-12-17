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

namespace SwagPromotion\Models\Repository;

use SwagPromotion\Struct\Promotion;

class DummyRepository implements Repository
{
    /**
     * @var Promotion[]
     */
    private $promotions;

    /**
     * DummyRepository constructor.
     *
     * @param Promotion[] $promotions
     */
    public function __construct($promotions)
    {
        $this->promotions = $promotions;
    }

    /**
     * {@inheritdoc}
     */
    public function getActivePromotions(
        $customerGroupId = null,
        $shopId = null,
        array $voucherIds = []
    ) {
        foreach ($this->promotions as $promotion) {
            if ($promotion->priority !== 1 || $promotion->exclusive !== 0) {
                break;
            }

            // no need for sorting
            return $this->promotions;
        }

        // sort promotions by priority and exclusive
        usort($this->promotions, $this->sortByPriorityAndExclusive());

        if ($customerGroupId === null) {
            return $this->promotions;
        }

        //Apply customer group filter
        $result = [];
        foreach ($this->promotions as $promotion) {
            if (in_array($customerGroupId, $promotion->customerGroups, false)) {
                $result[] = $promotion;
            }
        }

        return $result;
    }

    /**
     * @param Promotion[] $promotions
     */
    public function set($promotions)
    {
        $this->promotions = $promotions;
    }

    /**
     * {@inheritdoc}
     */
    public function getPromotionCounts($customerId)
    {
        $previousPromotionCounts = [];
        if ($customerId) {
            $sql = 'SELECT promotion_id, COUNT(promotion_id)
                    FROM s_plugin_promotion_customer_count
                    WHERE customer_id = :customerId
                    GROUP BY promotion_id;';
            $previousPromotionCounts = Shopware()->Db()->fetchPairs($sql, ['customerId' => $customerId]);
        }

        return $previousPromotionCounts;
    }

    /**
     * put together the priority and exclusive value and compare them
     * two usort() for each value in a row does not work, as the behaviour differs depending on the PHP version
     *
     * @return \Closure
     */
    private function sortByPriorityAndExclusive()
    {
        /*
         * @param Promotion $a
         * @param Promotion $b
         * @return int
         */
        return function ($a, $b) {
            // take the sum of priority and exclusive
            // exclusive is always 0 or 1, so the exclusive promotion with the highest priority is first element
            $valueA = (float) $a->priority + $a->exclusive;
            $valueB = (float) $b->priority + $b->exclusive;

            if ($valueA === $valueB) {
                return 0;
            }

            return ($valueA > $valueB) ? -1 : 1;
        };
    }
}
