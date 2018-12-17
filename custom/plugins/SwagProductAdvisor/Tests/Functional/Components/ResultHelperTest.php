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
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionMatch;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\AdvisorAttribute;
use SwagProductAdvisor\Components\Helper\ResultHelperInterface;
use SwagProductAdvisor\Models\Advisor;
use SwagProductAdvisor\Models\Question;
use SwagProductAdvisor\Tests\TestCase;

/**
 * Class ResultHelperTest
 */
class ResultHelperTest extends TestCase
{
    /**
     * @var ResultHelperInterface
     */
    public $resultHelper;

    /**
     * @var Advisor
     */
    public $advisor;

    /**
     * @var AdvisorService
     */
    public $advisorService;

    /**
     * Setup the necessary data.
     */
    public function setUp()
    {
        parent::setUp();

        $this->resultHelper = Shopware()->Container()->get('swag_product_advisor.result_helper');
        $this->advisor = $this::$helper->createAdvisor();

        /* @var AdvisorService $advisorService */
        $this->advisorService = Shopware()->Container()->get('swag_product_advisor.advisor_service');
    }

    /**
     * This test checks:
     *  ->getAdvisorResult()
     *  ->getTotalCount()
     *  ->getTopHit() # in the method getAdvisorResult
     */
    public function testGetAdvisorResult()
    {
        $selectedAnswers = $this::$helper->getAnsweredQuestions($this->advisor->getQuestions()->toArray());
        $advisor = $this->advisorService->get($this->advisor->getId(), $this::$productContext, $selectedAnswers);

        $result = $this->resultHelper->getAdvisorResult($advisor, $this::$request, $selectedAnswers);

        $this->assertNotNull($result);
        $this->assertNotNull($result->getTopHit());
        $this->assertNotNull($this->resultHelper->getTotalCount());
        $this->assertEquals($result->getId(), $advisor->getId());

        // prepare the next test
        $advisorWithoutTopHit = $this::$helper->createAdvisor(['highlightTopHit' => false]);
        $selectedAnswersWithoutTopHit = $this::$helper->getAnsweredQuestions($advisorWithoutTopHit->getQuestions()->toArray());
        $advisorWithoutTopHitResult = $this->advisorService->get($advisorWithoutTopHit->getId(), $this::$productContext, $selectedAnswersWithoutTopHit);

        $newResult = $this->resultHelper->getAdvisorResult(
            $advisorWithoutTopHitResult,
            $this::$request,
            $selectedAnswers
        );

        $newResultProducts = $newResult->getResult();

        $firstProduct = array_shift($newResultProducts);
        $topHit = $result->getTopHit();

        /** @var AdvisorAttribute $advisorAttribute */
        $advisorAttribute = $firstProduct['attributes']['advisor'];

        $this->assertEquals($firstProduct['articleID'], $topHit['articleID']);
        $this->assertNotEmpty($advisorAttribute);

        // Has either matches or misses
        $this->assertTrue($advisorAttribute->hasMatch() || !empty($advisorAttribute->getMisses()));
    }

    /**
     * This tests if a basic-question set with a default-boost of 1 has proper rankings in the result.
     */
    public function testSimpleAdvisorRanking()
    {
        $selectedAnswers = $this::$helper->getAnsweredQuestions($this->advisor->getQuestions()->toArray());
        $advisor = $this->advisorService->get($this->advisor->getId(), $this::$productContext, $selectedAnswers);
        $result = $this->resultHelper->getAdvisorResult($advisor, $this::$request, $selectedAnswers)->getResult();

        $this->assertTrue(count($result) > 0);
        foreach ($result as $product) {
            $productAttribute = $product['attributes'];
            $this->assertEquals($productAttribute['search']->get('advisorRanking'), count($productAttribute['advisor']->getMatches()));
        }
    }

    /**
     * This tests if the advisor-ranking is calculated properly when the questions have different boosts.
     */
    public function testAdvancedAdvisorRanking()
    {
        $advisor = $this::$helper->createAdvisor();
        $customBoosts = [
            'manufacturer' => 3,
            'property' => 2,
        ];

        // Set new questions with proper boost
        $questions = [
            $this::$helper->getQuestionHelper()->createManufacturerQuestion(['boost' => $customBoosts['manufacturer']]),
            $this::$helper->getQuestionHelper()->createPropertyQuestion(['boost' => $customBoosts['property']]),
        ];
        $advisor->setQuestions($questions);

        Shopware()->Models()->persist($advisor);
        Shopware()->Models()->flush();

        $selectedAnswers = $this->getAdvancedAnswers($advisor->getQuestions()->toArray());
        $advisor = $this->advisorService->get($advisor->getId(), $this::$productContext, $selectedAnswers);
        $result = $this->resultHelper->getAdvisorResult($advisor, $this::$request, $selectedAnswers)->getResult();

        $this->assertTrue(count($result) > 0);
        foreach ($result as $product) {
            $productAttributes = $product['attributes'];
            $productBoost = $this->getProductBoost($productAttributes, $customBoosts);

            $this->assertEquals($productBoost, $productAttributes['search']->get('advisorRanking'));
        }
    }

    public function testGetCriteria()
    {
        $advisor = $this->advisorService->get($this->advisor->getId(), $this::$productContext);
        $result = $this->resultHelper->getCriteria($advisor, $this::$request, $this::$productContext);

        $this->assertNotNull($result);
    }

    /**
     * This is a basic test if the paging is working.
     */
    public function testResultPaging()
    {
        $selectedAnswers = $this::$helper->getAnsweredQuestions($this->advisor->getQuestions()->toArray());
        $advisor = $this->advisorService->get($this->advisor->getId(), $this::$productContext, $selectedAnswers);
        $result = $this->resultHelper->getAdvisorResult($advisor, $this::$request, $selectedAnswers)->getResult();

        $this->assertNotNull($result);

        $pageOneFirstResult = reset($result);

        $this::$request->setParam('sPage', 2);
        $newResult = $this->resultHelper->getAdvisorResult($advisor, $this::$request, $selectedAnswers)->getResult();

        $this->assertNotNull($newResult);

        $pageTwoFirstResult = reset($newResult);

        $this->assertNotEquals($pageOneFirstResult['ordernumber'], $pageTwoFirstResult['ordernumber']);
    }

    /**
     * Returns a special set of given answers for the advanced ranking test.
     *
     * @param Question[] $questions
     *
     * @return array
     */
    private function getAdvancedAnswers(array $questions)
    {
        $manufacturerQuestion = $questions[3];
        $propertyQuestion = $questions[4];

        $propertyQuestionAnswerSet = [
            $propertyQuestion->getAnswers()[3]->getId(),
            $propertyQuestion->getAnswers()[4]->getId(),
        ];

        $manufacturerQuestionAnswerSet = [
            $manufacturerQuestion->getAnswers()[0]->getId(),
        ];

        $answerArray["q{$propertyQuestion->getId()}_values"] = implode('|', $propertyQuestionAnswerSet);
        $answerArray["q{$manufacturerQuestion->getId()}_values"] = implode('|', $manufacturerQuestionAnswerSet);

        return $answerArray;
    }

    /**
     * Helper method to calculate the total-boost by iterating over each match.
     * This is called once per product.
     *
     * @param array $productAttributes
     * @param array $customBoosts
     *
     * @return int
     */
    private function getProductBoost(array $productAttributes, array $customBoosts)
    {
        $productBoost = 0;
        if ($productAttributes['advisor']->hasMatch()) {
            /** @var QuestionMatch $match */
            foreach ($productAttributes['advisor']->getMatches() as $match) {
                if (!empty($customBoosts[$match['type']])) {
                    $productBoost += $customBoosts[$match['type']];
                }
            }
        }

        return $productBoost;
    }
}
