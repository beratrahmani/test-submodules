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

class FrontendControllerMock extends \Enlight_Controller_Action
{
    /**
     * @param \Enlight_View_Default                       $view
     * @param \Enlight_Controller_Request_RequestTestCase $request
     */
    public function __construct(\Enlight_View_Default $view, \Enlight_Controller_Request_RequestTestCase $request)
    {
        $this->view = $view;
        $this->request = $request;
    }
}
