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
 * ProductMatcher checks a given list of products against a given rule definition and returns
 * only those products, which do match the rule definition.
 */
class ProductMatcher
{
    /** @var array $product */
    protected $product;

    /**
     * ProductMatcher constructor.
     *
     * @param Registry    $registry
     * @param RuleBuilder $ruleBuilder
     */
    public function __construct(Registry $registry, RuleBuilder $ruleBuilder)
    {
        $this->registry = $registry;
        $this->ruleBuilder = $ruleBuilder;

        $this->registry->add(
            'productCompareRule',
            new Callback(
                [$this, 'productCompareRule']
            )
        );

        $this->registry->add(
            'stream',
            new Callback(
                [$this, 'streamRule']
            )
        );
    }

    /**
     * @param array $products
     * @param array $ruleDefinition
     *
     * @return array
     */
    public function getMatchingProducts(array $products, array $ruleDefinition)
    {
        $matches = [];
        foreach ($products as $product) {
            $this->product = $product;
            $rule = $this->ruleBuilder->fromArray($ruleDefinition);
            if ($rule->validate()) {
                $matches[] = $product;
            }
        }

        return $matches;
    }

    /**
     * @param array $product
     */
    public function setProduct(array $product)
    {
        $this->product = $product;
    }

    /**
     * @param array $config
     *
     * @return CompareRule
     */
    public function productCompareRule(array $config)
    {
        list($attributeName, $operator, $value) = $config;

        return new CompareRule([$this->product], $attributeName, $operator, $value);
    }

    /**
     * @param array $config
     *
     * @return StreamRule
     */
    public function streamRule(array $config)
    {
        return new StreamRule([$this->product], explode('|', $config[2]));
    }
}
