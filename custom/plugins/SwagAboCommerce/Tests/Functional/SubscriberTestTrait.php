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

namespace SwagAboCommerce\Tests\Functional;

use Enlight_Controller_Request_RequestTestCase as RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase as ResponseTestCase;
use Enlight_Template_Manager;
use SwagAboCommerce\Tests\Functional\Mocks\DummyControllerMock;
use SwagAboCommerce\Tests\Functional\Mocks\ViewMock;

trait SubscriberTestTrait
{
    /**
     * @var \Enlight_Controller_ActionEventArgs
     */
    private $controllerArgs;

    /**
     * @param bool $addResponse
     *
     * @return \Enlight_Controller_ActionEventArgs
     */
    public function getControllerEventArgs($addResponse = false)
    {
        if ($this->controllerArgs) {
            return $this->controllerArgs;
        }

        $request = $this->getDummyRequest();
        $response = null;
        if ($addResponse) {
            $response = $this->getDummyResponse();
        }

        $constructParameter = [
            'subject' => $this->getDummySubject($request, $response),
            'request' => $request,
            'response' => $response,
        ];

        return $this->controllerArgs = new \Enlight_Controller_ActionEventArgs($constructParameter);
    }

    /**
     * @param RequestTestCase       $request
     * @param ResponseTestCase|null $response
     *
     * @return DummyControllerMock
     */
    private function getDummySubject(RequestTestCase $request, ResponseTestCase $response = null)
    {
        return new DummyControllerMock(
            $request,
            new ViewMock(new Enlight_Template_Manager()),
            $response
        );
    }

    /**
     * @return RequestTestCase
     */
    private function getDummyRequest()
    {
        return new RequestTestCase();
    }

    /**
     * @return ResponseTestCase
     */
    private function getDummyResponse()
    {
        return new ResponseTestCase();
    }
}
