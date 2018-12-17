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

use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingArgumentMock;
use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingRequestMock;
use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingSubjectMock;
use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingViewMock;
use SwagLiveShopping\Tests\KernelTestCaseTrait;

class BackendSubscriberTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_backend_on_article_post_dispatch_load()
    {
        /** @var LiveShoppingViewMock $view */
        list($view, $subject, $arguments) = $this->getDefaultArguments('load');

        $backendSubscriber = $this->getContainer()->get('swag_liveshopping.backend_subscriber');

        $backendSubscriber->onBackendArticlePostDispatch($arguments);

        $expectedView = [
            'backend/article/view/detail/live_shopping_window.js',
        ];

        $this->assertArraySubset($expectedView, $view->templates);
    }

    public function test_backend_on_article_post_dispatch_index()
    {
        /** @var LiveShoppingViewMock $view */
        list($view, $subject, $arguments) = $this->getDefaultArguments();

        $backendSubscriber = $this->getContainer()->get('swag_liveshopping.backend_subscriber');

        $backendSubscriber->onBackendArticlePostDispatch($arguments);

        $expectedView = [
            'backend/article/live_shopping_app.js',
        ];

        $this->assertArraySubset($expectedView, $view->templates);
    }

    public function test_backend_on_index_post_dispatch()
    {
        /** @var LiveShoppingViewMock $view */
        list($view, $subject, $arguments) = $this->getDefaultArguments();

        $backendSubscriber = $this->getContainer()->get('swag_liveshopping.backend_subscriber');

        $backendSubscriber->onBackendIndexPostDispatch($arguments);

        $expectedView = [
            'backend/index/liveshopping_header.tpl',
        ];

        $this->assertArraySubset($expectedView, $view->templates);
    }

    /**
     * @param string $actionName
     *
     * @return array
     */
    private function getDefaultArguments($actionName = 'index')
    {
        $view = new LiveShoppingViewMock();
        $request = new LiveShoppingRequestMock($actionName);
        $subject = new LiveShoppingSubjectMock($view, $request);
        $arguments = new LiveShoppingArgumentMock($subject);

        return [$view, $subject, $arguments];
    }
}
