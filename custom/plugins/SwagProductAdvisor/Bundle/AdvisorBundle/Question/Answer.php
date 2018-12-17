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
 * Class Answer
 */
class Answer implements \JsonSerializable
{
    /**
     * @var string
     */
    private $answerId;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $label;

    /**
     * @var bool
     */
    private $selected;

    /**
     * @var string
     */
    private $css;

    /**
     * @var null|Media
     */
    private $media;

    /**
     * @var int
     */
    private $colId;

    /**
     * @var int
     */
    private $rowId;

    /**
     * PropertyAnswer constructor.
     *
     * @param string     $answerId
     * @param string     $key
     * @param string     $value
     * @param string     $label
     * @param string     $css
     * @param bool       $selected
     * @param null|Media $media
     * @param null       $colId
     * @param null       $rowId
     */
    public function __construct($answerId, $key, $value, $label, $selected, $css, $media = null, $colId = null, $rowId = null)
    {
        $this->answerId = $answerId;
        $this->key = $key;
        $this->value = $value;
        $this->label = $label;
        $this->selected = $selected;
        $this->css = $css;
        $this->media = $media;
        $this->colId = $colId;
        $this->rowId = $rowId;
    }

    /**
     * @return string
     */
    public function getAnswerId()
    {
        return $this->answerId;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
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
     * @return string
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * @return null|Media
     */
    public function getMedia()
    {
        return $this->media;
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
     * @return array
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
