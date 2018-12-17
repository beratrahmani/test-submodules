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

namespace SwagProductAdvisor\Components\Helper;

class AnswerBuilder implements AnswerBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildAnswers(array $params)
    {
        $params = $this->filterParams($params, function ($key) {
            $parts = explode('_', $key);

            if (!preg_match('/^q{1}[0-9]*_values(_[0-9]*|_min|_max)?$/', $key)) {
                return true;
            }

            return count($parts) <= 1 && !in_array('values', $parts, true);
        });

        $params = $this->formatCheckboxValues($params);

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function filterParams(array $params, $callback)
    {
        foreach ($params as $key => $value) {
            if ($callback($key, $value)) {
                unset($params[$key]);
                continue;
            }
        }

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getUniqueAnswers(array $answers)
    {
        foreach ($answers as $key => $answer) {
            $uniqueQuestionKey = reset(explode('_', $key));
            $answers[$uniqueQuestionKey] = $answer;
            unset($answers[$key]);
        }

        return $answers;
    }

    /**
     * We have to find out checkbox-answers and concatenate them into one string.
     * They are given like this:
     * [
     *    'q1_values' => '123' (Radio-Value)
     *    'q2_values_1' => 'xyz' (Checkbox-Value)
     *    'q2_values_2' => 'abc' (Checkbox-Value)
     * ]
     *
     * We have to iterate through each answer and then validate the key with a regex, which will validate if there is
     * an underscore followed by a number. This regex will only match for checkbox-values.
     * In the next step, we replace the last part, e.g. "_1", of they key to receive a summarized key.
     * Then we see if the "summarized key", e.g. "q2_values", has already been set and if this is true, we add a
     * '|' to separate the several answers properly.
     * Otherwise no pipe is added and it's the first answer to be saved.
     * Will return an array like this then:
     *
     * [
     *    'q1_values' => '123',
     *    'q2_values' => 'xyz|abc'
     * ]
     *
     * @param array $answers
     *
     * @return array
     */
    private function formatCheckboxValues(array $answers)
    {
        $regex = '/_[0-9]+/';

        foreach ($answers as $key => $answer) {
            if (is_array($answer)) {
                $answers[$key] = implode('|', $answer);
            } elseif ($this->isCheckBoxValue($regex, $key)) {
                $summarizedKey = $this->generateSummarizedKey($regex, $key);

                // If the key is already set, we need a separator to concatenate the values
                if (!empty($answers[$summarizedKey])) {
                    $answers[$summarizedKey] .= '|';
                }
                $answers[$summarizedKey] .= $answer;
                unset($answers[$key]);
            }
        }

        return $answers;
    }

    /**
     * @param string $regex
     * @param string $key
     *
     * @return bool
     */
    private function isCheckBoxValue($regex, $key)
    {
        return (bool) preg_match($regex, $key);
    }

    /**
     * @param string $regex
     * @param string $key
     *
     * @return string
     */
    private function generateSummarizedKey($regex, $key)
    {
        return preg_replace($regex, '', $key);
    }
}
