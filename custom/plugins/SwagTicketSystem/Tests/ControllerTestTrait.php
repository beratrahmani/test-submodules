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

namespace SwagTicketSystem\Tests;

trait ControllerTestTrait
{
    /**
     * @var \Enlight_Controller_Request_RequestTestCase
     */
    private $request;

    /**
     * @var \Enlight_Controller_Response_ResponseTestCase
     */
    private $response;

    /**
     * @var \Enlight_View_Default
     */
    private $view;

    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @return \Enlight_Controller_Request_RequestTestCase
     */
    public function Request()
    {
        if (!$this->request) {
            $this->request = new \Enlight_Controller_Request_RequestTestCase();
        }

        return $this->request;
    }

    /**
     * @return \Enlight_Controller_Response_ResponseTestCase
     */
    public function Response()
    {
        if (!$this->response) {
            $this->response = new \Enlight_Controller_Response_ResponseTestCase();
        }

        return $this->response;
    }

    /**
     * @return \Enlight_View_Default
     */
    public function View()
    {
        if (!$this->view) {
            $this->view = new \Enlight_View_Default(new \Enlight_Template_Manager());
        }

        return $this->view;
    }

    /**
     * @return \Shopware\Components\DependencyInjection\Container
     */
    public function Container()
    {
        return Shopware()->Container();
    }

    /**
     * @return \Enlight_Components_Session_Namespace|mixed
     */
    public function Session()
    {
        if (!$this->session) {
            $this->session = $this->Container()->get('session');
        }

        return $this->session;
    }
}
