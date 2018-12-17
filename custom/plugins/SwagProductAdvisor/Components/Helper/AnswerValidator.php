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

use Shopware\Components\Model\ModelManager;

class AnswerValidator implements AnswerValidatorInterface
{
    /**
     * @var array
     */
    private $question;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * AnswerValidator constructor.
     *
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAnswer($answerKey, $answerValue)
    {
        $this->question = $this->getQuestionByKey($answerKey);

        if (!$this->question) {
            return false;
        }

        if (!$this->validatePriceQuestion($answerKey)) {
            return false;
        }

        if (!$this->validateGeneralQuestion($answerKey)) {
            return false;
        }

        if (!$this->validateMultipleAnswers($answerValue)) {
            return false;
        }

        return true;
    }

    /**
     * Validates if a price-answer is valid.
     *
     * @param string $answerKey
     *
     * @return bool
     */
    private function validatePriceQuestion($answerKey)
    {
        // This is no price-question, so we don't need to validate anything here
        if (!$this->isPriceAnswer($answerKey)) {
            return true;
        }

        if (!$this->isPriceQuestion()) {
            return false;
        }

        return $this->validatePriceSliderAnswer($answerKey);
    }

    /**
     * Validates if a general-answer is valid.
     * It might have changed to a price-question and therefore could result in invalid data being saved in the database.
     *
     * @param string $answerKey
     *
     * @return bool
     */
    private function validateGeneralQuestion($answerKey)
    {
        if ($this->isPriceAnswer($answerKey)) {
            return true;
        }

        return !$this->isPriceQuestion();
    }

    /**
     * Validates if an answer is a price-slider-answer and therefore if the matching question is still a range-slider.
     *
     * @param string $answerKey
     *
     * @return bool
     */
    private function validatePriceSliderAnswer($answerKey)
    {
        if ($this->isPriceSliderAnswer($answerKey)) {
            return $this->isPriceQuestionSlider();
        }

        return $this->isPriceAnswer($answerKey);
    }

    /**
     * Validates if multiple answers are allowed, if any given.
     *
     * @param string $answerValue
     *
     * @return bool
     */
    private function validateMultipleAnswers($answerValue)
    {
        if (strpos($answerValue, '|') === false) {
            return true;
        }

        return $this->isMultipleAnswerQuestion();
    }

    /**
     * Reads the whole question by the given key.
     *
     * @param string $answerKey
     *
     * @return array
     */
    private function getQuestionByKey($answerKey)
    {
        $builder = $this->modelManager->getConnection()->createQueryBuilder();

        return $builder->select('*')
            ->from('s_plugin_product_advisor_question', 'question')
            ->where('question.id = :questionId')
            ->setParameter('questionId', $this->getQuestionIdByKey($answerKey))
            ->execute()
            ->fetch();
    }

    /**
     * Checks if a price-answer is still valid.
     * The price-question might have been changed to another type of question
     * meanwhile, which could result in invalid data.
     *
     * @return bool
     */
    private function isPriceQuestion()
    {
        return $this->question['type'] === 'price';
    }

    /**
     * Checks if a question is a price-slider-question.
     *
     * @return bool
     */
    private function isPriceQuestionSlider()
    {
        return $this->question['template'] === 'range_slider';
    }

    /**
     * Checks if a question is a price-slider-question.
     *
     * @return bool
     */
    private function isMultipleAnswerQuestion()
    {
        return (int) $this->question['multiple_answers'] === 1;
    }

    /**
     * Extracts the question-id from the given key.
     *
     * @param string $answerKey
     *
     * @return string
     */
    private function getQuestionIdByKey($answerKey)
    {
        preg_match('/q([0-9]+)/', $answerKey, $matches);

        return $matches[1];
    }

    /**
     * Returns true if the current answers seems to be a price-slider-answer.
     *
     * @param string $answerKey
     *
     * @return bool
     */
    private function isPriceSliderAnswer($answerKey)
    {
        return strpos($answerKey, 'min') !== false;
    }

    /**
     * Returns true if the question seems to be a price-question.
     *
     * @param string $answerKey
     *
     * @return bool
     */
    private function isPriceAnswer($answerKey)
    {
        return strpos($answerKey, 'min') !== false || strpos($answerKey, 'max') !== false;
    }
}
