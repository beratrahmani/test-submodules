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

namespace SwagPromotion\Tests\Functional;

use Shopware\Components\Test\Plugin\TestCase;
use SwagPromotion\Components\BasketRuleBuilder;
use SwagPromotion\Components\Rules\CompareRule;
use SwagPromotion\Components\Rules\Registry\Registry;
use SwagPromotion\Components\Rules\Rule\Container\AndRule;
use SwagPromotion\Components\Rules\Rule\Container\NotRule;
use SwagPromotion\Components\Rules\Rule\Container\OrRule;
use SwagPromotion\Components\Rules\Rule\Container\XorRule;
use SwagPromotion\Components\Rules\Rule\FalseRule;
use SwagPromotion\Components\Rules\Rule\TrueRule;
use SwagPromotion\Components\Rules\StreamRule;
use SwagPromotion\Tests\Functional\Components\Fixtures\Arguments;

class BasketRuleBuilderTest extends TestCase
{
    private $rules = [
        'and' => AndRule::class,
        'or' => OrRule::class,
        'xor' => XorRule::class,
        'not' => NotRule::class,
        'false' => FalseRule::class,
        'true' => TrueRule::class,
        'productCompareRule' => CompareRule::class,
        'basketCompareRule' => CompareRule::class,
        'customerCompareRule' => CompareRule::class,
        'stream' => StreamRule::class,
    ];

    public function test_create()
    {
        $arguments = new Arguments();

        $basketRuleBuilderClass = new \ReflectionClass(BasketRuleBuilder::class);
        $basketRuleBuilderProperty = $basketRuleBuilderClass->getProperty('registry');
        $basketRuleBuilderProperty->setAccessible(true);

        $ruleBuilder = new BasketRuleBuilder();
        $ruleBuilder->create($arguments->getBasket(), $arguments->getProducts(), $arguments->getCustomer());

        $result = $basketRuleBuilderProperty->getValue($ruleBuilder);

        foreach ($this->rules as $rule => $instance) {
            $instanceResult = $result->get($rule);

            $this->assertInstanceOf($instance, $instanceResult);
        }

        $this->assertInstanceOf(Registry::class, $result);
    }

    public function test_create_should_throw_exception()
    {
        $arguments = new Arguments();

        $basketRuleBuilderClass = new \ReflectionClass(BasketRuleBuilder::class);
        $basketRuleBuilderProperty = $basketRuleBuilderClass->getProperty('registry');
        $basketRuleBuilderProperty->setAccessible(true);

        $ruleBuilder = new BasketRuleBuilder();
        $ruleBuilder->create($arguments->getBasket(), $arguments->getProducts(), $arguments->getCustomer());

        $result = $basketRuleBuilderProperty->getValue($ruleBuilder);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Rule notExistentRule not found');

        $result->get('notExistentRule');
    }
}
