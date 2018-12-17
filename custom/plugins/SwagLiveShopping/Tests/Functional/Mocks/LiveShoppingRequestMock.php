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

use Enlight_Controller_Request_RequestHttp;

class LiveShoppingRequestMock extends Enlight_Controller_Request_RequestHttp
{
    /**
     * @var string
     */
    public $actionName;

    /**
     * @var array
     */
    public $params;

    /**
     * @param string $actionName
     */
    public function __construct($actionName)
    {
        $this->actionName = $actionName;
        $this->params = [];
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @param array $params
     *
     * @return $this|\Zend_Controller_Request_Http
     */
    public function setParams(array $params)
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return LiveShoppingRequestMock
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $key
     * @param null  $default
     *
     * @return mixed|null
     */
    public function getParam($key, $default = null)
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }

        return $default;
    }
}
