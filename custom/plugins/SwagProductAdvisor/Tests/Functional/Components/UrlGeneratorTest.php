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

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\AdvisorService;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\Advisor as AdvisorStruct;
use SwagProductAdvisor\Components\Helper\ResultHelperInterface;
use SwagProductAdvisor\Components\Helper\UrlGeneratorInterface;
use SwagProductAdvisor\Models\Advisor;
use SwagProductAdvisor\Tests\TestCase;

/***
 * Class UrlGeneratorTest
 *
 * @package SwagProductAdvisor\Tests
 */
class UrlGeneratorTest extends TestCase
{
    /**
     * @var Advisor
     */
    public $advisor;

    /**
     * @var string
     */
    public $hash;

    /**
     * @var UrlGeneratorInterface
     */
    public $urlGenerator;

    /**
     * @var AdvisorService
     */
    public $advisorService;

    /**
     * @var ResultHelperInterface
     */
    public $resultHelper;

    /**
     * Setup the necessary data.
     */
    public function setUp()
    {
        parent::setUp();

        $this->advisor = $this::$helper->createAdvisor();
        $this->urlGenerator = Shopware()->Container()->get('swag_product_advisor.url_generator');
        $this->resultHelper = Shopware()->Container()->get('swag_product_advisor.result_helper');
        $this->hash = md5('So Long, and Thanks for All the Fish.');

        /** @var ContextServiceInterface $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');
        $this::$productContext = $contextService->getProductContext();

        /* @var AdvisorService $advisorService */
        $this->advisorService = Shopware()->Container()->get('swag_product_advisor.advisor_service');
    }

    /**
     * Test the generatePreviousQuestionUrl method of the UrlGenerator
     */
    public function testGeneratePreviousQuestionUrl()
    {
        /** @var AdvisorStruct $advisor */
        $advisor = $this->advisorService->get($this->advisor->getId(), $this::$productContext);

        $questions = $advisor->getQuestions();

        $currentQuestion = $questions[1];
        $prevQuestion = $questions[0];

        $expectedResult = 'advisor/question/advisorId/' .
            $advisor->getId() .
            '/questionId/' .
            $prevQuestion->getId() .
            '/hash/d5b91fdeebce59e6fc74546b0e459088';

        $advisor->setCurrentQuestion($currentQuestion);
        $result = $this->urlGenerator->generatePreviousQuestionUrl($advisor, $this->hash);

        $this->assertContains($expectedResult, $result);
    }

    /**
     * Test the generateNextQuestionUrl method of the UrlGenerator
     */
    public function testGenerateNextQuestionUrl()
    {
        /** @var AdvisorStruct $advisor */
        $advisor = $this->advisorService->get($this->advisor->getId(), $this::$productContext);

        $questions = $advisor->getQuestions();

        $currentQuestion = $questions[0];
        $nextQuestion = $questions[1];

        $expectedResult = 'advisor/save/advisorId/' .
            $advisor->getId() .
            '/questionId/' .
            $nextQuestion->getId() .
            '/hash/d5b91fdeebce59e6fc74546b0e459088';

        $advisor->setCurrentQuestion($currentQuestion);
        $result = $this->urlGenerator->generateNextQuestionUrl($advisor, $this->hash);

        $this->assertContains($expectedResult, $result);
    }
}
