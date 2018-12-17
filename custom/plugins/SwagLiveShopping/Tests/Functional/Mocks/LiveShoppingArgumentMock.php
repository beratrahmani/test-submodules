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

class LiveShoppingArgumentMock extends \Enlight_Controller_ActionEventArgs
{
    /**
     * @var LiveShoppingSubjectMock
     */
    public $subjectMock;

    /**
     * @var mixed
     */
    public $returnData;

    /**
     * @var mixed
     */
    public $element;

    /**
     * @param LiveShoppingSubjectMock $subjectMock
     */
    public function __construct(LiveShoppingSubjectMock $subjectMock)
    {
        $this->subjectMock = $subjectMock;
    }

    /**
     * @return LiveShoppingSubjectMock
     */
    public function getSubject()
    {
        return $this->subjectMock;
    }

    /**
     * @param string $key
     *
     * @return null|LiveShoppingSubjectMock
     */
    public function get($key)
    {
        if ($key === 'subject') {
            return $this->subjectMock;
        }

        return null;
    }

    /**
     * @param mixed $data
     */
    public function setReturn($data)
    {
        $this->returnData = $data;
    }

    /**
     * @return mixed
     */
    public function getReturn()
    {
        return $this->returnData;
    }

    /**
     * @return mixed
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @param mixed $data
     */
    public function setElement($data)
    {
        $this->element = $data;
    }

    /**
     * @return \Enlight_Controller_Response_ResponseHttp|LiveShoppingRequestMock
     */
    public function getRequest()
    {
        return $this->subjectMock->Request();
    }
}
