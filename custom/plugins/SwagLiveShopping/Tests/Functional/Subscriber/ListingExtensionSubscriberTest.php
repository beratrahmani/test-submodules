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

namespace SwagLiveShopping\Tests\Functional\Subscriber;

use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use SwagLiveShopping\Subscriber\ListingExtensionSubscriber;
use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingArgumentMock;
use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingRequestMock;
use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingSubjectMock;
use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingViewMock;
use SwagLiveShopping\Tests\KernelTestCaseTrait;

class ListingExtensionSubscriberTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_onBackendProductStreamPostDispatch()
    {
        /** @var LiveShoppingViewMock $view */
        /** @var LiveShoppingSubjectMock $subject */
        /** @var LiveShoppingArgumentMock $arguments */
        list($view, $subject, $arguments) = $this->getDefaultArguments();

        $subscriber = $this->getListingExtensionSubscriber();
        $subscriber->onBackendProductStreamPostDispatch($arguments);

        $expectedView = [
            'backend/product_stream/view/condition_list/live_shopping_condition_panel.js',
        ];

        $this->assertArraySubset($expectedView, $view->templates);
    }

    public function test_onBackendConfig()
    {
        /** @var LiveShoppingViewMock $view */
        /** @var LiveShoppingSubjectMock $subject */
        /** @var LiveShoppingArgumentMock $arguments */
        list($view, $subject, $arguments) = $this->getDefaultArguments();
        $subscriber = $this->getListingExtensionSubscriber();
        $subscriber->onBackendConfig($arguments);

        $expectedView = [
            'backend/config/live_shopping_extension.js',
        ];

        $this->assertArraySubset($expectedView, $view->templates);
    }

    public function test_assignLiveShoppingFromProduct_action_name_is_not_index()
    {
        /** @var LiveShoppingViewMock $view */
        /** @var LiveShoppingSubjectMock $subject */
        /** @var LiveShoppingArgumentMock $arguments */
        list($view, $subject, $arguments) = $this->getDefaultArguments();
        $request = new LiveShoppingRequestMock('NotIndex');
        $subject->setRequest($request);

        $subscriber = $this->getListingExtensionSubscriber();
        $subscriber->assignLiveShoppingFromProduct($arguments);

        $this->expectException(\Exception::class);
        $view->getAssign('liveShopping');
    }

    public function test_assignLiveShoppingFromProduct_action_name_index_no_article()
    {
        /** @var LiveShoppingViewMock $view */
        /** @var LiveShoppingSubjectMock $subject */
        /** @var LiveShoppingArgumentMock $arguments */
        list($view, $subject, $arguments) = $this->getDefaultArguments();
        $request = new LiveShoppingRequestMock('index');
        $subject->setRequest($request);

        $view->assign('sArticle', ['attributes' => []]);

        $subscriber = $this->getListingExtensionSubscriber();
        $subscriber->assignLiveShoppingFromProduct($arguments);

        $this->expectException(\Exception::class);
        $view->getAssign('liveShopping');
    }

    public function test_assignLiveShoppingFromProduct_there_should_be_a_view_assign()
    {
        /** @var LiveShoppingViewMock $view */
        /** @var LiveShoppingSubjectMock $subject */
        /** @var LiveShoppingArgumentMock $arguments */
        list($view, $subject, $arguments) = $this->getDefaultArguments();
        $request = new LiveShoppingRequestMock('index');
        $subject->setRequest($request);

        $view->assign(
            'sArticle',
            ['attributes' => ['live_shopping' => new Attribute(['live_shopping' => ['a', 'b', 'c']])]]
        );

        $subscriber = $this->getListingExtensionSubscriber();
        $subscriber->assignLiveShoppingFromProduct($arguments);

        $result = $view->getAssign('liveShopping');

        $this->assertEquals(['a', 'b', 'c'], $result);
    }

    /**
     * @return ListingExtensionSubscriber
     */
    private function getListingExtensionSubscriber()
    {
        return new ListingExtensionSubscriber();
    }

    /**
     * @return array
     */
    private function getDefaultArguments()
    {
        $view = new LiveShoppingViewMock();
        $subject = new LiveShoppingSubjectMock($view);
        $arguments = new LiveShoppingArgumentMock($subject);

        return [$view, $subject, $arguments];
    }
}
