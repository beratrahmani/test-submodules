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

use Shopware\Components\Test\Plugin\TestCase;
use SwagPromotion\Components\Rules\CompareRule;

/**
 * @small
 */
class CompareRuleTest extends TestCase
{
    public function compareRuleDataProvider()
    {
        return [
            [['categories' => [1, 2, 3, 4, 5]], 'categories', 'in', [5], true],
            [['categories' => [['id' => 5]]], 'categories.id', 'in', [5], true],
            [['categories' => 5], 'categories', 'in', 5, true],
            [['categories' => [1, 2, 3, 4, 5]], 'categories', 'notin', [55], true],
            [['categories' => 5], 'categories', 'notin', 55, true],
            [['attr1' => 'test'], 'attr1', '=', 'test', true],
            [['attr1' => 'Hi, this is a test'], 'attr1', 'contains', 'this', true],
            [['attr1' => 'Hi, this is a test'], 'attr1', 'notcontains', 'foooo', true],
            [['number' => 3], 'number', '>', 2, true],
            [['number' => 3], 'number', '<', 4, true],
            [['number' => 3], 'number', '>=', 3, true],
            [['number' => 3], 'number', '<=', 3, true],
            [['number' => 3], 'number', '!=', 4, true],
            [['string' => 'foo'], 'foo', '!=', 'bar', true],
            [['bool' => true], 'bool', 'istrue', '', true],
            [['bool' => false], 'bool', 'isfalse', '', true],

            [['categories' => [1, 2, 3, 4, 5]], 'categories', 'in', [55], false],
            [[], 'categories', '=', [5], false],
            [['categories' => 5], 'categories', 'in', 55, false],
            [['categories' => [1, 2, 3, 4, 5]], 'categories', 'notin', [5], false],
            [['categories' => 5], 'categories', 'notin', 5, false],
            [['attr1' => 'test'], 'attr1', '=', 'something', false],
            [['attr1' => 'Hi, this is a test'], 'attr1', 'contains', 'foooo', false],
            [['attr1' => 'Hi, this is a test'], 'attr1', 'notcontains', 'this', false],
            [['number' => 3], 'number', '>', 4, false],
            [['number' => 3], 'number', '<', 2, false],
            [['number' => 3], 'number', '>=', 4, false],
            [['number' => 3], 'number', '<=', 2, false],
            [['number' => 3], 'number', '!=', 3, false],
            [['string' => 'foo'], 'string', '!=', 'foo', false],
            [['bool' => false], 'bool', 'istrue', '', false],
            [['bool' => true], 'bool', 'isfalse', '', false],
        ];
    }

    /**
     * @dataProvider compareRuleDataProvider
     *
     * @param $data
     * @param $fieldName
     * @param $operator
     * @param $value
     * @param $expectedResult
     */
    public function testCompareRule($data, $fieldName, $operator, $value, $expectedResult)
    {
        $rule = new CompareRule(
            $data ? [$data] : [],
            $fieldName,
            $operator,
            $value
        );

        $actualResult = $rule->validate();
        $this->assertTrue($expectedResult === $actualResult, "Expected $expectedResult, got $actualResult");
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testUnknownOperatorShouldThrowException()
    {
        $rule = new CompareRule(
            [
                ['bar' => [1, 2]],
            ],
            'bar',
            'fo',
            '3'
        );
        $rule->validate();
    }
}
