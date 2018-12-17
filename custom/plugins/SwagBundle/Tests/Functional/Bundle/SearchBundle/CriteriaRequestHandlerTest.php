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

namespace SwagBundle\Tests\Functional\Bundle\SearchBundle;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagBundle\Bundle\SearchBundle\CriteriaRequestHandler;

class CriteriaRequestHandlerTest extends TestCase
{
    public function test_handleRequest_should_add_condition_bundle_base_param_given()
    {
        $criteriaRequestHandler = new CriteriaRequestHandler();

        /** @var ContextServiceInterface $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');

        /** @var StoreFrontCriteriaFactoryInterface $criteriaFactory */
        $criteriaFactory = Shopware()->Container()->get('shopware_search.store_front_criteria_factory');

        /** @var \Enlight_Controller_Request_RequestHttp $request */
        $request = Shopware()->Container()->get('front')->Request();
        $shopContext = $contextService->getShopContext();

        $request->setParam('bundle_base', true);

        $criteria = $criteriaFactory->createListingCriteria(
            $request,
            $contextService->getShopContext()
        );

        $criteriaRequestHandler->handleRequest(
            $request,
            $criteria,
            $shopContext
        );

        self::assertTrue($criteria->hasCondition('bundle'));

        $request->setParam('bundle_base', false);
    }

    public function test_handleRequest_should_add_condition_bundle_param_given()
    {
        $criteriaRequestHandler = new CriteriaRequestHandler();

        /** @var ContextServiceInterface $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');

        /** @var StoreFrontCriteriaFactoryInterface $criteriaFactory */
        $criteriaFactory = Shopware()->Container()->get('shopware_search.store_front_criteria_factory');

        /** @var \Enlight_Controller_Request_RequestHttp $request */
        $request = Shopware()->Container()->get('front')->Request();
        $shopContext = $contextService->getShopContext();

        $request->setParam('bundle', true);

        $criteria = $criteriaFactory->createListingCriteria(
            $request,
            $contextService->getShopContext()
        );

        $criteriaRequestHandler->handleRequest(
            $request,
            $criteria,
            $shopContext
        );

        self::assertTrue($criteria->hasCondition('bundle'));

        $request->setParam('bundle', false);
    }
}
