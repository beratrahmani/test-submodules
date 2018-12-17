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

use SwagProductAdvisor\Models\Advisor;

/**
 * Class TranslationService
 */
interface TranslationServiceInterface
{
    /**
     * Translates the basic advisor-data.
     *
     * @param array $advisor
     *
     * @return array
     */
    public function translateBasic(array $advisor);

    /**
     * Iterates through the questions and translates each of them.
     *
     * @param array $questionData
     *
     * @return array
     */
    public function translateQuestions(array $questionData);

    /**
     * Translates the single given question.
     * Only the info-text and the question-text itself are translatable.
     * It also iterates through the answers and triggers the correct translation-method.
     *
     * @param array $question
     *
     * @return array
     */
    public function translateQuestion(array $question);

    /**
     * Translates the answers for the price-slider.
     * The configuration of the answer is also translated here to be handled properly.
     *
     * @param array $question
     *
     * @return array
     */
    public function translatePriceSliderAnswers(array $question);

    /**
     * Translates the price-steps for a default-price question.
     *
     * @param array $steps
     *
     * @return array
     */
    public function translatePriceSteps(array $steps);

    /**
     * This translates the answers for property-, attribute- and supplier-questions.
     *
     * @param array $answers
     *
     * @return array
     */
    public function translateDefaultAnswers(array $answers);

    /**
     * This clones the whole advisor-translations.
     * The basic-translations as well as the question- and answer-translations are considered.
     *
     * @param Advisor $newAdvisor
     * @param Advisor $oldAdvisor
     */
    public function cloneTranslations(Advisor $newAdvisor, Advisor $oldAdvisor);

    /**
     * Clones the basic advisor translations
     *
     * @param string $newId
     * @param string $oldId
     */
    public function cloneAdvisorBasic($newId, $oldId);

    /**
     * Clones a question with its answers
     *
     * @param \Traversable $newQuestions
     * @param \Traversable $oldQuestions
     */
    public function cloneQuestions(\Traversable $newQuestions, \Traversable $oldQuestions);

    /**
     * This clones the translations from $oldQuestion to $newQuestion.
     * This only works with arrays, not with Question-Objects!
     *
     * @param array $oldQuestion
     * @param array $newQuestion
     */
    public function cloneQuestionToNewId(array $oldQuestion, array $newQuestion);

    /**
     * Checks if the raw question data contains a translationCloneId.
     * This is necessary to clone translations when cloning a single question.
     * If the translationCloneId is given, it will try to clone the translations to the new question.
     * Therefore we need the parent-data, which will contain the new id already.
     *
     * @param array $rawQuestionData
     * @param array $parentData
     */
    public function checkForTranslationClone(array $rawQuestionData, array $parentData);
}
