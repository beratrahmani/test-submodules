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
use SwagProductAdvisor\Components\Helper\AdvisorValidator;
use SwagProductAdvisor\Models\Advisor;
use SwagProductAdvisor\Tests\TestCase;

/**
 * Class AdvisorValidatorTest
 */
class AdvisorValidatorTest extends TestCase
{
    /** @var Advisor $advisor */
    public $advisor;

    /** @var AdvisorService $advisorService */
    public $advisorService;

    /** @var AdvisorValidator $advisorValidator */
    private $advisorValidator;

    /**
     * Setup the necessary data.
     */
    public function setUp()
    {
        parent::setUp();
        $this->advisor = $this::$helper->createAdvisor();
        $this->advisor->setQuestions([
            $this::$helper->getQuestionHelper()->createAttributeQuestion(),
            $this::$helper->getQuestionHelper()->createPropertyQuestion(),
            $this::$helper->getQuestionHelper()->createManufacturerQuestion(['needsToBeAnswered' => true]),
        ]);

        Shopware()->Models()->persist($this->advisor);
        Shopware()->Models()->flush();

        $this->advisorValidator = Shopware()->Container()->get('swag_product_advisor.advisor_validator');
        $this->advisorService = Shopware()->Container()->get('swag_product_advisor.advisor_service');
    }

    /**
     * Validates if the "required fields"-validation works as expected.
     */
    public function testValidateRequiredAdvisor()
    {
        $questions = $this->advisor->getQuestions()->toArray();
        $selectedAnswers = $this::$helper->getAnsweredQuestions(array_values($questions));
        $advisor = $this->advisorService->get($this->advisor->getId(), $this::$productContext, $selectedAnswers);
        $errors = $this->advisorValidator->validateAdvisor($advisor, 'requiredFields');

        $this->assertTrue(count($errors) === 0);

        // We remove the manufacturer-answer to create an invalid answer-set
        array_pop($selectedAnswers);
        $falseAdvisor = $this->advisorService->get($this->advisor->getId(), $this::$productContext, $selectedAnswers);

        $errors = $this->advisorValidator->validateAdvisor($falseAdvisor, 'requiredFields');
        $this->assertTrue(count($errors) > 0);
    }
}
