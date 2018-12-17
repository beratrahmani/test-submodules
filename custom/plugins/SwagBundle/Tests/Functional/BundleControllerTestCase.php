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

namespace SwagBundle\Tests\Functional;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use PHPUnit\Framework\TestCase;

class BundleControllerTestCase extends TestCase
{
    /**
     * @var Enlight_Controller_Request_RequestTestCase
     */
    private $request;

    /**
     * @var Enlight_Controller_Response_ResponseTestCase
     */
    private $response;

    /**
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function Request()
    {
        if (!$this->request) {
            $this->request = new Enlight_Controller_Request_RequestTestCase();
        }

        return $this->request;
    }

    /**
     * @return Enlight_Controller_Response_ResponseTestCase
     */
    public function Response()
    {
        if (!$this->response) {
            $this->response = new Enlight_Controller_Response_ResponseTestCase();
        }

        return $this->response;
    }
}
