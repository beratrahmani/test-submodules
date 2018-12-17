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

namespace SwagPromotion\Components;

use SwagPromotion\Components\Rules\CompareRule;
use SwagPromotion\Components\Rules\Registry\Registry;
use SwagPromotion\Components\Rules\Registry\Type\Callback;
use SwagPromotion\Components\Rules\RuleBuilder;
use SwagPromotion\Components\Rules\StreamRule;

/**
 * BasketRuleBuilder creates a rule builder and populates it with the required rules
 */
class BasketRuleBuilder
{
    /** @var Registry $registry */
    private $registry;

    /** @var RuleBuilder $ruleBuilder */
    private $ruleBuilder;

    /**
     * BasketRuleBuilder constructor.
     */
    public function __construct()
    {
        $this->registry = new Registry();
        $this->ruleBuilder = new RuleBuilder($this->registry);
    }

    /**
     * @param array $basket
     * @param array $products
     * @param array $customer
     *
     * @return RuleBuilder
     */
    public function create(array $basket, array $products, array $customer)
    {
        $this->registry->add(
            'productCompareRule',
            new Callback(
                function ($config) use ($products) {
                    list($attributeName, $operator, $value) = $config;

                    return new CompareRule($products, $attributeName, $operator, $value);
                }
            )
        );

        $this->registry->add(
            'basketCompareRule',
            new Callback(
                function ($config) use ($basket) {
                    list($attributeName, $operator, $value) = $config;

                    return new CompareRule([$basket], $attributeName, $operator, $value);
                }
            )
        );

        $this->registry->add(
            'customerCompareRule',
            new Callback(
                function ($config) use ($customer) {
                    list($attributeName, $operator, $value) = $config;

                    return new CompareRule([$customer], $attributeName, $operator, $value);
                }
            )
        );

        $this->registry->add(
            'stream',
            new Callback(
                function ($config) use ($products, $basket) {
                    return new StreamRule($products, explode('|', $config[2]));
                }
            )
        );

        return $this->ruleBuilder;
    }
}
