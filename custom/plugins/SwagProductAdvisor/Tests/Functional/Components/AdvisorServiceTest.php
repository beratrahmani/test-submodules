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
use SwagProductAdvisor\Models\Advisor;
use SwagProductAdvisor\Tests\TestCase;

/**
 * Class AdvisorServiceTest
 */
class AdvisorServiceTest extends TestCase
{
    /** @var Advisor $advisor */
    public $advisor;

    /**
     * Setup the necessary data.
     */
    public function setUp()
    {
        parent::setUp();
        $this->advisor = $this::$helper->createAdvisor();
    }

    public function testGetAdvisor()
    {
        /** @var ContextServiceInterface $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');
        $productContext = $contextService->createProductContext(1);

        /** @var AdvisorService $advisorService */
        $advisorService = Shopware()->Container()->get('swag_product_advisor.advisor_service');
        $advisor = $advisorService->get($this->advisor->getId(), $productContext);

        // TEST's
        $this->assertEquals($this->advisor->getId(), $advisor->getId());
        $this->assertEquals(count($this->advisor->getQuestions()), count($advisor->getQuestions()));

        $originalQuestions = $this->advisor->getQuestions();

        $this->assertTrue(count($advisor->getQuestions()) > 0);

        // Check for correct order of questions
        foreach ($advisor->getQuestions() as $key => $question) {
            $currentOriginalQuestion = $originalQuestions[$key];
            $this->assertEquals($question->getId(), $currentOriginalQuestion->getId());
            $this->assertEquals($question->getType(), $currentOriginalQuestion->getType());
        }
    }
}
