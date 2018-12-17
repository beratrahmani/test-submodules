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

use Doctrine\ORM\PersistentCollection;
use SwagProductAdvisor\Bundle\AdvisorBundle\AdvisorService;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\PriceQuestion\PriceDefaultQuestion;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\PriceQuestion\PriceRangeQuestion;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\Step;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\AdvisorAttribute;
use SwagProductAdvisor\Components\Helper\ResultHelperInterface;
use SwagProductAdvisor\Tests\TestCase;

/**
 * Class QuestionTests
 */
class QuestionTest extends TestCase
{
    /** @var AdvisorService $advisorService */
    public $advisorService;

    /** @var ResultHelperInterface $resultBuilder */
    public $resultHelper;

    /**
     * Setup the necessary data.
     */
    public function setUp()
    {
        parent::setUp();

        $this->advisorService = Shopware()->Container()->get('swag_product_advisor.advisor_service');
        $this->resultHelper = Shopware()->Container()->get('swag_product_advisor.result_helper');
    }

    /**
     * Simple test for a attribute-, property- or manufacturer-question.
     * This will only test if setting up an advisor with a basic-question will also result in matches / misses.
     */
    public function testBasicQuestions()
    {
        foreach ($this::$helper->getQuestionHelper()->getMixedQuestions() as $currentQuestion) {
            $advisor = $this::$helper->createAdvisor(['minMatchingAttributes' => 0]);
            $questionArray = [$currentQuestion];

            $advisor->setQuestions($questionArray);

            Shopware()->Models()->persist($advisor);
            Shopware()->Models()->flush();

            $selectedAnswers = $this->getBasicAnswers($advisor->getQuestions());
            $advisor = $this->advisorService->get($advisor->getId(), $this::$productContext, $selectedAnswers);
            $result = $this->resultHelper->getAdvisorResult($advisor, $this::$request, $selectedAnswers)->getResult();

            $this->assertTrue(count($result) > 0);
            foreach ($result as $product) {
                /** @var AdvisorAttribute $productAdvisorAttribute */
                $productAdvisorAttribute = $product['attributes']['advisor'];
                // Check if current product has at least a match or a miss
                $this->assertTrue($productAdvisorAttribute->hasMatch() || count($productAdvisorAttribute->getMisses()) > 0);
            }
        }
    }

    /**
     * Simple test for a default price-question.
     * This will only test if setting up a price-question will contain all necessary data.
     * In this case we need "steps" to be part of the question-content.
     */
    public function testDefaultPriceQuestion()
    {
        $question = $this::$helper->getQuestionHelper()->createDefaultPriceQuestion();
        $advisor = $this::$helper->createAdvisor();
        $advisor->setQuestions([$question]);

        Shopware()->Models()->persist($advisor);
        Shopware()->Models()->flush();

        $advisor = $this->advisorService->get($advisor->getId(), $this::$productContext);
        $firstQuestion = reset($advisor->getQuestions());

        $this->assertInstanceOf(
            PriceDefaultQuestion::class,
            $firstQuestion
        );

        $this->assertTrue(count($firstQuestion->getSteps()) > 0);
        foreach ($firstQuestion->getSteps() as $step) {
            $this->assertInstanceOf(Step::class, $step);
            $this->assertNotEmpty($step->getValue());
        }
    }

    /**
     * Simple test for a range price-question.
     * This will only test if setting up a price-question will contain all necessary data.
     * In this case we need the "min" and "max"-values to be part of the question-content.
     */
    public function testRangePriceQuestion()
    {
        $question = $this::$helper->getQuestionHelper()->createRangePriceQuestion();
        $advisor = $this::$helper->createAdvisor();
        $advisor->setQuestions([$question]);

        Shopware()->Models()->persist($advisor);
        Shopware()->Models()->flush();

        $advisor = $this->advisorService->get($advisor->getId(), $this::$productContext);
        $firstQuestion = reset($advisor->getQuestions());

        $this->assertInstanceOf(
            PriceRangeQuestion::class,
            $firstQuestion
        );
        $this->assertNotEmpty($firstQuestion->getMinCss());
    }

    /**
     * Builds an example attribute-, property- or manufacturer-answer setup.
     *
     * @param PersistentCollection $questions
     *
     * @return array
     */
    private function getBasicAnswers(PersistentCollection $questions)
    {
        $question = $questions->first();

        return [
            "q{$question->getId()}_values" => $question->getAnswers()[1]->getId(),
        ];
    }
}
