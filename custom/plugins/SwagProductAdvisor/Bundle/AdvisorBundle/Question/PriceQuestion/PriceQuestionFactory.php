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

namespace SwagProductAdvisor\Bundle\AdvisorBundle\Question\PriceQuestion;

use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionFactoryInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\Step;

/**
 * Class PriceQuestionFactory
 */
class PriceQuestionFactory implements QuestionFactoryInterface
{
    /**
     * @param array $data
     *
     * @return bool
     */
    public function supports(array $data)
    {
        return $data['type'] === 'price';
    }

    /**
     * @param array                $data
     * @param ShopContextInterface $context
     * @param array                $post
     *
     * @return QuestionInterface
     */
    public function factory(array $data, ShopContextInterface $context, array $post)
    {
        $selectedMin = 0;
        $selectedMax = 0;

        if (isset($post['min'])) {
            $selectedMin = $post['min'];
        }
        if (isset($post['max'])) {
            $selectedMax = $post['max'];
        }

        if (strtolower($data['template']) === 'range_slider') {
            return $this->createRangeQuestion($data, $post, $selectedMin, $selectedMax);
        }

        return $this->createSelectionQuestion($data, $post, $selectedMin, $selectedMax);
    }

    /**
     * @param array $data
     * @param array $post
     * @param float $selectedMin
     * @param float $selectedMax
     *
     * @return PriceRangeQuestion
     */
    protected function createRangeQuestion(array $data, array $post, $selectedMin, $selectedMax)
    {
        $question = new PriceRangeQuestion(
            $data['id'],
            $data['question'],
            $data['template'],
            $data['type'],
            $data['exclude'],
            $data['configuration']['minCss'],
            $data['configuration']['maxCss'],
            $data['configuration']['min'],
            $data['configuration']['max'],
            $selectedMin,
            $selectedMax,
            !empty($post['min']) || !empty($post['max']),
            $data['info_text'],
            $data['needs_to_be_answered'],
            $data['expand_question'],
            $data['boost']
        );

        return $question;
    }

    /**
     * @param array $data
     * @param array $post
     * @param float $selectedMin
     * @param float $selectedMax
     *
     * @return PriceDefaultQuestion
     */
    protected function createSelectionQuestion(array $data, array $post, $selectedMin, $selectedMax)
    {
        $steps = [];
        foreach ($data['steps'] as $step) {
            $isAnswered = false;
            if ($step['price'] == $post['max']) {
                $isAnswered = true;
            }
            $steps[] = new Step($step['price'], $step['guid'], $step['css'], $step['label'], $step['rowId'], $step['colId'], $isAnswered, $step['media']);
        }

        $question = new PriceDefaultQuestion(
            $data['id'],
            $data['question'],
            $data['template'],
            $data['type'],
            $data['exclude'],
            $steps,
            $selectedMin,
            $selectedMax,
            !empty($post),
            $data['info_text'],
            $data['needs_to_be_answered'],
            $data['expand_question'],
            $data['boost'],
            $data['number_of_columns'],
            $data['number_of_rows'],
            $data['column_height'],
            $data['hide_text']
        );

        return $question;
    }
}
