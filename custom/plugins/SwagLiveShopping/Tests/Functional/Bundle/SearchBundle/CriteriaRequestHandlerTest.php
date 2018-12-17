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

namespace Shopware\SwagLiveShopping\Tests\Functional\Bundle\SearchBundle;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagLiveShopping\Bundle\SearchBundle\Condition\LiveShoppingCondition;
use SwagLiveShopping\Bundle\SearchBundle\CriteriaRequestHandler;
use SwagLiveShopping\Tests\KernelTestCaseTrait;

class CriteriaRequestHandlerTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_handleRequest_should_add_live_shopping_condition()
    {
        $criteriaRequestHandler = $this->getCriteriaRequestHandler();

        $request = new \Enlight_Controller_Request_RequestHttp();
        $criteria = new Criteria();

        /** @var ContextServiceInterface $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');

        $request->setParam('live', true);

        $criteriaRequestHandler->handleRequest(
            $request,
            $criteria,
            $contextService->getShopContext()
        );

        $expectedCriteria = new Criteria();
        $expectedCriteria->addCondition(new LiveShoppingCondition());

        $this->assertCount(1, $criteria->getConditions());
        $this->assertEquals($expectedCriteria->getConditions(), $criteria->getConditions());
    }

    public function test_handleRequest_should_add_base_live_shopping_condition()
    {
        $criteriaRequestHandler = $this->getCriteriaRequestHandler();

        $request = new \Enlight_Controller_Request_RequestHttp();
        $criteria = new Criteria();

        /** @var ContextServiceInterface $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');

        $request->setParam('live_base', 'foo');

        $criteriaRequestHandler->handleRequest(
            $request,
            $criteria,
            $contextService->getShopContext()
        );

        $expectedCriteria = new Criteria();
        $expectedCriteria->addBaseCondition(new LiveShoppingCondition());

        $this->assertCount(1, $criteria->getBaseConditions());
        $this->assertEquals($expectedCriteria->getBaseConditions(), $criteria->getBaseConditions());
    }

    public function test_handleRequest_should_not_add_live_shopping_facet()
    {
        $criteriaRequestHandler = $this->getCriteriaRequestHandler();

        $request = new \Enlight_Controller_Request_RequestHttp();
        $criteria = new Criteria();

        /** @var ContextServiceInterface $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');

        $criteriaRequestHandler->handleRequest(
            $request,
            $criteria,
            $contextService->getShopContext()
        );

        $this->assertCount(0, $criteria->getFacets());
    }

    /**
     * @return CriteriaRequestHandler
     */
    private function getCriteriaRequestHandler()
    {
        return new CriteriaRequestHandler();
    }
}
