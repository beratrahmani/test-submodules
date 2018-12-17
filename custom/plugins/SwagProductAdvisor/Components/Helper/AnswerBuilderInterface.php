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

interface AnswerBuilderInterface
{
    /**
     * Helper method to build a valid answer-array from the given request-parameters.
     * Additionally converts the checkbox-answers into the default-structure.
     *
     * @param array $params
     *
     * @return array
     */
    public function buildAnswers(array $params);

    /**
     * Filters the given params by a callback-method.
     * array_filter can't be used here, as we need the array-key in the callback.
     *
     * @param array    $params
     * @param callable $callback
     *
     * @return array
     */
    public function filterParams(array $params, $callback);

    /**
     * Filters duplicated answers for a question, e.g. when using a range-slider, we might have values from
     * q1_values_min to q1_values_max. We only want one answer per question to count the real questions being given.
     *
     * @param array $answers
     *
     * @return array
     */
    public function getUniqueAnswers(array $answers);
}
