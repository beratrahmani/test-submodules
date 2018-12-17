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

namespace SwagPromotion\Tests\Rules;

use PHPUnit_Framework_TestCase;
use SwagPromotion\Components\Rules\Registry\Registry;
use SwagPromotion\Components\Rules\Registry\Type\Callback;
use SwagPromotion\Components\Rules\Rule;
use SwagPromotion\Components\Rules\RuleBuilder;
use SwagPromotion\Tests\Helper\BasketAmountRule;

/**
 * @small
 */
class RuleBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testRuleBuilder()
    {
        $registry = new Registry();
        $currentBasketAmount = 199;
        $registry->add(
            'maxAmountWithCallable',
            new Callback(
                function ($maxAmount) use ($currentBasketAmount) {
                    return new BasketAmountRule($currentBasketAmount, $maxAmount);
                }
            )
        );
        $registry->add('true', new Rule\TrueRule());
        $registry->add('false', new Rule\FalseRule());

        $builder = new RuleBuilder($registry);

        $result = $builder->fromArray(
            [
                'and' => [
                    'maxAmountWithCallable' => [200],
                    new Rule\TrueRule(),
                ],
                'or' => [
                    'false',
                    new Rule\TrueRule(),
                ],
            ]
        );

        $this->assertTrue($result->validate());
    }

    public function testRuleBuilderWithArrayValue()
    {
        $registry = new Registry();
        $registry->add(
            'test',
            new Callback(
                function ($x) {
                    return new Rule\TrueRule();
                }
            )
        );

        $builder = new RuleBuilder($registry);
        $builder->fromArray(
            [
                'test' => [1, 2, 3],
            ]
        );
    }
}
