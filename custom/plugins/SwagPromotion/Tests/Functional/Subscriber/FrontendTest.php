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

namespace SwagPromotion\Tests\Functional\Subscriber;

use Shopware\Components\DependencyInjection\Container;
use SwagPromotion\Subscriber\Frontend;
use SwagPromotion\Tests\PromotionTestCase;

class FrontendTest extends PromotionTestCase
{
    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        parent::setUp();

        $this->container = Shopware()->Container();
    }

    public function test_getSubscribedEvents()
    {
        $result = Frontend::getSubscribedEvents();

        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result);
        $this->assertCount(7, $result);
    }

    public function test_onPostDispatchFrontendDetail()
    {
        $product = [];
        $product['hasNewPromotionProductPrice'] = true;

        $view = new FrontendTestViewMock();
        $view->assign('sArticle', $product);

        $subject = new FrontendTestSubjectMock($view);
        $arguments = new FrontendTestArgumentsMock($subject);
        $subscriber = $this->getSubscriber();
        $this->setPriceDisplaying($subscriber, 'price');

        $subscriber->onPostDispatchFrontendDetail($arguments);

        $articleResult = $view->getAssign('sArticle');
        $expectedSubset = [
            'promotionPriceDisplaying' => 'price',
        ];

        $this->assertTrue($articleResult['has_pseudoprice']);
        $this->assertArraySubset($expectedSubset, $view->viewAssign);
    }

    public function test_onPostDispatchFrontendDetail_no_new_price()
    {
        $product = [];
        $product['hasNewPromotionProductPrice'] = false;

        $view = new FrontendTestViewMock();
        $view->assign('sArticle', $product);

        $subject = new FrontendTestSubjectMock($view);
        $arguments = new FrontendTestArgumentsMock($subject);
        $subscriber = $this->getSubscriber();
        $this->setPriceDisplaying($subscriber, 'price');

        $subscriber->onPostDispatchFrontendDetail($arguments);
        $articleResult = $view->getAssign('sArticle');

        $this->assertNull($articleResult['has_pseudoprice']);
    }

    public function test_onPostDispatchFrontendListing()
    {
        $product = [];
        $product['hasNewPromotionProductPrice'] = true;
        $view = new FrontendTestViewMock();
        $view->assign('sArticles', [$product]);

        $subject = new FrontendTestSubjectMock($view);
        $arguments = new FrontendTestArgumentsMock($subject);
        $subscriber = $this->getSubscriber();
        $this->setPriceDisplaying($subscriber, 'price');

        $subscriber->onPostDispatchFrontendListing($arguments);

        $articleResult = $view->getAssign('sArticles');
        $expectedSubset = [
            'promotionPriceDisplaying' => 'price',
        ];

        $this->assertTrue($articleResult[0]['has_pseudoprice']);
        $this->assertArraySubset($expectedSubset, $view->viewAssign);
    }

    public function test_onPostDispatchFrontendListing_with_empty_products()
    {
        $view = new FrontendTestViewMock();

        $subject = new FrontendTestSubjectMock($view);
        $arguments = new FrontendTestArgumentsMock($subject);
        $subscriber = $this->getSubscriber();
        $this->setPriceDisplaying($subscriber, 'price');

        $test = $subscriber->onPostDispatchFrontendListing($arguments);

        $this->assertNull($test);
    }

    public function test_onFetchListing()
    {
        $product = [];
        $product['hasNewPromotionProductPrice'] = true;
        $view = new FrontendTestViewMock();
        $view->assign('sArticles', [$product]);

        $subject = new FrontendTestSubjectMock($view);
        $arguments = new FrontendTestArgumentsMock($subject);
        $subscriber = $this->getSubscriber();
        $this->setPriceDisplaying($subscriber, 'price');

        $subscriber->onFetchListing($arguments);

        $articleResult = $view->getAssign('sArticles');
        $expectedSubset = [
            'promotionPriceDisplaying' => 'price',
        ];

        $this->assertTrue($articleResult[0]['has_pseudoprice']);
        $this->assertArraySubset($expectedSubset, $view->viewAssign);
    }

    public function test_onFetchListing_with_empty_products()
    {
        $view = new FrontendTestViewMock();

        $subject = new FrontendTestSubjectMock($view);
        $arguments = new FrontendTestArgumentsMock($subject);
        $subscriber = $this->getSubscriber();
        $this->setPriceDisplaying($subscriber, 'price');

        $test = $subscriber->onFetchListing($arguments);

        $this->assertNull($test);
    }

    public function test_onFetchListing_without_price_setting()
    {
        $view = new FrontendTestViewMock();

        $subject = new FrontendTestSubjectMock($view);
        $arguments = new FrontendTestArgumentsMock($subject);
        $subscriber = $this->getSubscriber();
        $this->setPriceDisplaying($subscriber, 'foo');

        $test = $subscriber->onFetchListing($arguments);

        $this->assertNull($test);
    }

    public function test_onPostDispatchFrontendCompare()
    {
        $product = [];
        $product['hasNewPromotionProductPrice'] = true;

        $compareList = [];
        $compareList['articles'] = [$product];

        $view = new FrontendTestViewMock();
        $view->assign('sComparisonsList', $compareList);

        $subject = new FrontendTestSubjectMock($view);

        $arguments = new FrontendTestArgumentsMock($subject);
        $subscriber = $this->getSubscriber();
        $this->setPriceDisplaying($subscriber, 'price');

        $subscriber->onPostDispatchFrontendCompare($arguments);

        $compareListResult = $view->getAssign('sComparisonsList');
        $articleResult = $compareListResult['articles'];
        $expectedSubset = [
            'promotionPriceDisplaying' => 'price',
        ];

        $this->assertTrue($articleResult[0]['has_pseudoprice']);
        $this->assertArraySubset($expectedSubset, $view->viewAssign);
    }

    public function test_onPostDispatchFrontendCompare_with_empty_products()
    {
        $view = new FrontendTestViewMock();

        $subject = new FrontendTestSubjectMock($view);
        $arguments = new FrontendTestArgumentsMock($subject);
        $subscriber = $this->getSubscriber();
        $this->setPriceDisplaying($subscriber, 'price');

        $test = $subscriber->onPostDispatchFrontendCompare($arguments);

        $this->assertNull($test);
    }

    /**
     * @return Frontend
     */
    private function getSubscriber()
    {
        return new Frontend(
            'SwagPromotion',
            $this->container->get('shopware.plugin.cached_config_reader')
        );
    }

    /**
     * @param Frontend $subscriber
     * @param $priceDisplaying
     */
    private function setPriceDisplaying(Frontend $subscriber, $priceDisplaying)
    {
        $reflectionClass = new \ReflectionClass(Frontend::class);
        $property = $reflectionClass->getProperty('priceDisplaying');
        $property->setAccessible(true);
        $property->setValue($subscriber, $priceDisplaying);
    }
}

class FrontendTestArgumentsMock extends \Enlight_Controller_ActionEventArgs
{
    /**
     * @param FrontendTestSubjectMock $subject
     */
    public function __construct($subject)
    {
        $this->set('subject', $subject);
    }
}

class FrontendTestSubjectMock
{
    /**
     * @var FrontendTestViewMock
     */
    public $view;

    /**
     * @var FrontendTestRequestMock
     */
    public $request;

    /**
     * @param FrontendTestViewMock    $view
     * @param FrontendTestRequestMock $request
     */
    public function __construct($view, $request = null)
    {
        $this->view = $view;
        $this->request = $request;
    }

    /**
     * @return FrontendTestViewMock
     */
    public function View()
    {
        return $this->view;
    }

    /**
     * @return FrontendTestRequestMock
     */
    public function Request()
    {
        return $this->request;
    }
}

class FrontendTestRequestMock
{
    /**
     * @var string
     */
    public $actionName;

    /**
     * @param string $actionName
     */
    public function __construct($actionName)
    {
        $this->actionName = $actionName;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }
}

class FrontendTestViewMock extends \Enlight_View_Default
{
    /**
     * @var array
     */
    public $viewAssign = [];

    /**
     * @var string
     */
    public $templateDir;

    public function __construct()
    {
        $this->viewAssign = [];
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param null   $nocache
     * @param null   $scope
     */
    public function assign($key, $value = null, $nocache = null, $scope = null)
    {
        $this->viewAssign[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getAssign($key = null)
    {
        return $this->viewAssign[$key];
    }

    /**
     * @param      $templateDir
     * @param null $key
     */
    public function addTemplateDir($templateDir, $key = null)
    {
        $this->templateDir = $templateDir;
    }
}
