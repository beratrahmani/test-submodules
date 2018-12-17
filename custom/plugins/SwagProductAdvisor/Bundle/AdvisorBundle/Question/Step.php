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

namespace SwagProductAdvisor\Bundle\AdvisorBundle\Question;

use Shopware\Bundle\StoreFrontBundle\Struct\Media;

/**
 * Class Step
 */
class Step implements \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $answerId;

    /**
     * @var string
     */
    private $css;

    /**
     * @var string
     */
    private $label;

    /**
     * @var bool
     */
    private $selected;

    /**
     * @var int
     */
    private $colId;

    /**
     * @var int
     */
    private $rowId;

    /**
     * @var null|Media
     */
    private $media;

    /**
     * PropertyAnswer constructor.
     *
     * @param string $value
     * @param string $answerId
     * @param string $css
     * @param string $label
     * @param null   $rowId
     * @param null   $colId
     * @param bool   $selected
     * @param null   $media
     */
    public function __construct($value, $answerId, $css, $label, $rowId = null, $colId = null, $selected = false, $media = null)
    {
        $this->value = (int) $value;
        $this->answerId = $answerId;
        $this->css = $css;
        $this->label = $label;
        $this->colId = $colId;
        $this->rowId = $rowId;
        $this->selected = $selected;
        $this->media = $media;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getAnswerId()
    {
        return $this->answerId;
    }

    /**
     * @return string
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function isSelected()
    {
        return $this->selected;
    }

    /**
     * @return null|int
     */
    public function getColId()
    {
        return $this->colId;
    }

    /**
     * @return null|int
     */
    public function getRowId()
    {
        return $this->rowId;
    }

    /**
     * @return null|Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
