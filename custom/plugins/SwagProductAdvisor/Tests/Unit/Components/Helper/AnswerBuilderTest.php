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

namespace SwagProductAdvisor\Tests\Unit\Components\Helper;

use SwagProductAdvisor\Components\Helper\AnswerBuilder;

/**
 * Class AnswerBuilderTest
 */
class AnswerBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the buildAnswer method of the AnswerBuilder
     */
    public function test_buildAnswers()
    {
        $expectedResult = [
            'q107_values' => '8|9|11|12|13',
            'q111_values' => '25|26',
            'q112_values' => '29',
            'q108_values' => '14',
            'q109_values' => '18',
            'q110_values' => '23',
            'q127_values' => '140',
            'q129_values' => '233',
            'q128_values' => '185|45|46|47',
            'q130_values' => '48|49',
        ];

        $fixtures = [
            'q107_values_1' => '8',
            'q107_values_2' => '9',
            'q107_values_4' => '11',
            'q107_values_5' => '12',
            'q107_values_6' => '13',
            'q111_values_0' => '25',
            'q111_values_1' => '26',
            'q112_values' => '29',
            'q108_values' => '14',
            'q109_values' => '18',
            'q110_values' => '23',
            'q127_values' => '140',
            'q128_values' => '185',
            'q129_values' => '233',
            'q128_values_1' => '45',
            'q128_values_2' => '46',
            'q128_values_3' => '47',
            'q130_values' => ['48', '49'],
            // add some fakes
            'qq12_values' => '42',
            'bq123_values_x' => 'Who is Batman',
            'bq123_values_y' => 'Who is Superman',
            'bq123_values_z' => 'Who is Spongebob',
        ];

        $answerBuilder = $this->getAnswerBuilder();

        $result = $answerBuilder->buildAnswers($fixtures);

        foreach ($expectedResult as $key => $value) {
            $this->assertEquals($result[$key], $value);
        }

        $this->assertEquals(count($expectedResult), count($result));
    }

    public function test_filterParams_should_filter_odd_keys()
    {
        $answerBuilder = $this->getAnswerBuilder();

        $params = [
            2 => 'FooA',
            4 => 'FooB',
            5 => 'FoC',
            7 => 'ooD',
        ];

        $resultSet = $answerBuilder->filterParams($params, function ($key) {
            return $key % 2 === 1;
        });

        self::assertCount(2, $resultSet);
        self::assertArrayHasKey(2, $resultSet);
        self::assertArrayHasKey(4, $resultSet);
    }

    public function test_getUniqueAnswers_should_drop_duplicated_answers()
    {
        $answerBuilder = $this->getAnswerBuilder();

        $answers = [
            'q1_foo' => 'bar_1',
            'q1_bar' => 'baz_1',
            'q2_foo' => 'bar_2',
            'q3_foo' => 'bar_3',
            'q3_bar' => 'baz_3',
        ];

        $resultSet = $answerBuilder->getUniqueAnswers($answers);

        self::assertCount(3, $resultSet);
        self::assertArrayHasKey('q1', $resultSet);
        self::assertArrayHasKey('q2', $resultSet);
        self::assertArrayHasKey('q3', $resultSet);
        self::assertEquals('baz_1', $resultSet['q1']);
        self::assertEquals('baz_3', $resultSet['q3']);
    }

    /**
     * @return AnswerBuilder
     */
    private function getAnswerBuilder()
    {
        return new AnswerBuilder();
    }
}
