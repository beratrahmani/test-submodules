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

namespace SwagProductAdvisor\Tests\Functional\Components;

use SwagProductAdvisor\Bundle\AdvisorBundle\AdvisorService;
use SwagProductAdvisor\Components\Helper\AnswerValidatorInterface;
use SwagProductAdvisor\Tests\TestCase;

/**
 * Class AnswerValidatorTest
 */
class AnswerValidatorTest extends TestCase
{
    /** @var AdvisorService $advisorService */
    public $advisorService;

    /** @var AnswerValidatorInterface $answerValidator */
    public $answerValidator;

    /**
     * Setup the necessary data.
     */
    public function setUp()
    {
        parent::setUp();
        $this->answerValidator = Shopware()->Container()->get('swag_product_advisor.answer_validator');
    }

    /**
     * Tests if the answer-validator returns true if completely valid answers are given.
     */
    public function testValidAnswers()
    {
        $advisorModel = $this::$helper->createAdvisor();
        $selectedAnswers = $this::$helper->getAnsweredQuestions(array_values($advisorModel->getQuestions()->toArray()));

        foreach ($selectedAnswers as $answerKey => $answer) {
            $this->assertTrue($this->answerValidator->validateAnswer($answerKey, $answer));
        }
    }

    /**
     * Tests the following scenario:
     * - Answer looks like this: q123_values_max = 15, so it must be a price-answer
     * - The question is a non-price-question.
     *
     * This has to result in an invalid validation, so it asserts false.
     */
    public function testPriceAnswerToGeneralQuestion()
    {
        $answerSet = $this->getPriceAnswerToGeneralQuestion();
        foreach ($answerSet as $answerKey => $answer) {
            $this->assertFalse($this->answerValidator->validateAnswer($answerKey, $answer));
        }
    }

    /**
     * Tests the following scenario:
     * - Answer looks like this: q123_values_min = 15, so this can only be a price-slider-answer, due to the "min".
     * - Question is a default price-question. no price-slider question.
     *
     * Asserts false here, because this is invalid.
     */
    public function testPriceSliderAnswerToPriceDefaultQuestion()
    {
        $answerSet = $this->getPriceSliderAnswerToPriceDefaultQuestion();
        foreach ($answerSet as $answerKey => $answer) {
            $this->assertFalse($this->answerValidator->validateAnswer($answerKey, $answer));
        }
    }

    /**
     * Tests the scenario when a general answer is given for a price-question:
     * - Answer looks like this: q456_values, this is a general answer.
     * - Question is a price-question.
     *
     * This is invalid and therefore this asserts false.
     */
    public function testGeneralAnswerToPriceQuestion()
    {
        $answerSet = $this->getGeneralAnswerToPriceQuestion();
        foreach ($answerSet as $answerKey => $answer) {
            $this->assertFalse($this->answerValidator->validateAnswer($answerKey, $answer));
        }
    }

    /**
     * Tests the validation when setting multiple answers to a simple question:
     * Answer looks like this: q456_values = 12|13, so this is only possible for "allow multiple answers"-questions
     * The question itself doesn't allow multiple answers.
     *
     * Therefore this is invalid and the validation has to return false.
     */
    public function testMultipleAnswerToSimpleQuestion()
    {
        $answerSet = $this->getMultipleAnswerToSimpleQuestion();
        foreach ($answerSet as $answerKey => $answer) {
            $this->assertFalse($this->answerValidator->validateAnswer($answerKey, $answer));
        }
    }

    /**
     * Sets up an advisor with a question, which doesn't allow multiple answers.
     * Yet this returns an answer-set with multiple-answers.
     *
     * @return array
     */
    public function getMultipleAnswerToSimpleQuestion()
    {
        $advisor = $this::$helper->createAdvisor();
        $advisor->setQuestions([$this::$helper->getQuestionHelper()->createManufacturerQuestion()]);

        Shopware()->Models()->persist($advisor);
        Shopware()->Models()->flush();

        $question = $advisor->getQuestions()->first();
        $answerSet = [
            "q{$question->getId()}_values" => '123|456',
        ];

        return $answerSet;
    }

    /**
     * Sets up a default-advisor with a mixed question-set.
     * Returns a price-answer, even though there is no price-question defined in the question-set.
     *
     * @return array
     */
    private function getPriceAnswerToGeneralQuestion()
    {
        $advisor = $this::$helper->createAdvisor();

        $question = $advisor->getQuestions()->first();
        $answerSet = [
            "q{$question->getId()}_values_max" => '15',
        ];

        return $answerSet;
    }

    /**
     * Sets up an advisor with a default-price question and then returns a price-slider-answer.
     *
     * @return array
     */
    private function getPriceSliderAnswerToPriceDefaultQuestion()
    {
        $advisor = $this::$helper->createAdvisor();
        $advisor->setQuestions([$this::$helper->getQuestionHelper()->createDefaultPriceQuestion()]);

        Shopware()->Models()->persist($advisor);
        Shopware()->Models()->flush();

        $question = $advisor->getQuestions()->first();
        $answerSet = [
            "q{$question->getId()}_values_min" => '15',
        ];

        return $answerSet;
    }

    /**
     * Sets up an advisor with a default-price-question and returns an answer for a general non-price-question.
     *
     * @return array
     */
    private function getGeneralAnswerToPriceQuestion()
    {
        $advisor = $this::$helper->createAdvisor();
        $advisor->setQuestions([$this::$helper->getQuestionHelper()->createDefaultPriceQuestion()]);

        Shopware()->Models()->persist($advisor);
        Shopware()->Models()->flush();

        $question = $advisor->getQuestions()->first();
        $answerSet = [
            "q{$question->getId()}_values" => 'AnyString',
        ];

        return $answerSet;
    }
}
