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

namespace SwagLiveShopping\Tests\Functional\Mocks;

require_once __DIR__ . '/../../../Controllers/Backend/LiveShopping.php';

use Shopware_Controllers_Backend_LiveShopping;

class BackendLiveShoppingMock extends Shopware_Controllers_Backend_LiveShopping
{
    /**
     * @var LiveShoppingRequestMock
     */
    public $request;

    /**
     * @var LiveShoppingResponseMock
     */
    public $response;

    /**
     * @var LiveShoppingViewMock
     */
    public $view;

    /**
     * @param LiveShoppingRequestMock  $request
     * @param LiveShoppingResponseMock $response
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = new LiveShoppingViewMock();
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return Shopware()->Container()->get($key);
    }
}
