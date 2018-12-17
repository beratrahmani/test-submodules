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

namespace SwagBundle\Tests\Functional\Mocks;

use Shopware\Components\DependencyInjection\Container;

class BundleFrontendControllerMock extends \Shopware_Controllers_Frontend_Bundle
{
    /**
     * @param \Enlight_Controller_Request_RequestTestCase   $request
     * @param \Enlight_Controller_Response_ResponseTestCase $response
     * @param Container                                     $container
     * @param \Enlight_View_Default                         $view
     */
    public function __construct(
        \Enlight_Controller_Request_RequestTestCase $request,
        \Enlight_Controller_Response_ResponseTestCase $response,
        Container $container,
        \Enlight_View_Default $view
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->container = $container;
        $this->view = $view;
    }
}
