<?php declare(strict_types=1);

namespace Shopware\B2B\Price\Bridge;

use Shopware\B2B\Price\Framework\PriceEntity;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit;

class StoreFrontBundlePriceRuleFactory
{
    /**
     * @param PriceEntity $price
     * @param Group $group
     * @param Unit $unit
     * @return PriceRule
     */
    public function create(PriceEntity $price, Group $group = null, Unit $unit = null): PriceRule
    {
        $priceRuleStruct = new PriceRule();
        $priceRuleStruct->setId($price->id);
        $priceRuleStruct->setPrice($price->price);
        $priceRuleStruct->setFrom($price->from);

        if ($price->to) {
            $priceRuleStruct->setTo($price->to);
        } else {
            $priceRuleStruct->setTo(null);
        }

        $priceRuleStruct->setCustomerGroup($group);
        $priceRuleStruct->setUnit($unit);

        return $priceRuleStruct;
    }

    /**
     * @param PriceRule $lastRule
     * @param mixed $price
     * @return PriceRule
     */
    public function generateLastRuleByPriceRule(PriceRule $lastRule, $price): PriceRule
    {
        $newLastRule = new PriceRule();

        $newLastRule->setFrom($lastRule->getTo() + 1);
        $newLastRule->setTo(null);
        $newLastRule->setPrice($price);

        $newLastRule->setCustomerGroup($lastRule->getCustomerGroup());
        $newLastRule->setUnit($lastRule->getUnit());

        return $newLastRule;
    }

    /**
     * @param PriceRule $firstRule
     * @param mixed $price
     * @return PriceRule
     */
    public function generateFirstRuleByPriceRule(PriceRule $firstRule, $price): PriceRule
    {
        $newFirstRule = new PriceRule();

        $newFirstRule->setFrom(1);
        $newFirstRule->setTo($firstRule->getFrom() - 1);
        $newFirstRule->setPrice($price);

        $newFirstRule->setCustomerGroup($firstRule->getCustomerGroup());
        $newFirstRule->setUnit($firstRule->getUnit());

        return $newFirstRule;
    }
}
