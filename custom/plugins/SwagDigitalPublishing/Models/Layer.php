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

namespace SwagDigitalPublishing\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Digital Publishing Layer
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 *
 * @ORM\Table(name="s_digital_publishing_layers")
 * @ORM\Entity()
 */
class Layer extends ModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var ContentBanner
     *
     * @ORM\ManyToOne(targetEntity="SwagDigitalPublishing\Models\ContentBanner", inversedBy="layers")
     * @ORM\JoinColumn(name="contentBannerID", referencedColumnName="id")
     */
    protected $contentBanner;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="SwagDigitalPublishing\Models\Element",
     *     mappedBy="layer",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    protected $elements;
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $label;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $orientation;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $width;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $height;

    /**
     * @var string
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $marginTop;

    /**
     * @var string
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $marginRight;

    /**
     * @var string
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $marginBottom;

    /**
     * @var string
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $marginLeft;

    /**
     * @var string
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $borderRadius;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $bgColor;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $link;

    /**
     * @var int
     *
     * @ORM\Column(name="contentBannerID", type="integer", nullable=false)
     */
    private $contentBannerID;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->elements = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = null;
        $this->contentBannerID = null;

        $elements = [];

        foreach ($this->getElements() as $element) {
            $newElement = clone $element;
            $newElement->setLayer($this);

            $elements[] = $newElement;
        }

        $this->elements = $elements;
    }

    /**
     * @return string
     */
    public function getBgColor()
    {
        return $this->bgColor;
    }

    /**
     * @param string $bgColor
     */
    public function setBgColor($bgColor)
    {
        $this->bgColor = $bgColor;
    }

    /**
     * @return ContentBanner
     */
    public function getContentBanner()
    {
        return $this->contentBanner;
    }

    /**
     * @param ContentBanner $contentBanner
     */
    public function setContentBanner($contentBanner)
    {
        $this->contentBanner = $contentBanner;
    }

    /**
     * @return ArrayCollection
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param array $elements
     */
    public function setElements($elements)
    {
        $this->setOneToMany($elements, Element::class, 'elements', 'layer');
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param string $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * @param string $orientation
     */
    public function setOrientation($orientation)
    {
        $this->orientation = $orientation;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param string $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return string
     */
    public function getBorderRadius()
    {
        return $this->borderRadius;
    }

    /**
     * @param string $borderRadius
     */
    public function setBorderRadius($borderRadius)
    {
        $this->borderRadius = $borderRadius;
    }

    /**
     * @return string
     */
    public function getMarginBottom()
    {
        return $this->marginBottom;
    }

    /**
     * @param string $marginBottom
     */
    public function setMarginBottom($marginBottom)
    {
        $this->marginBottom = $marginBottom;
    }

    /**
     * @return string
     */
    public function getMarginLeft()
    {
        return $this->marginLeft;
    }

    /**
     * @param string $marginLeft
     */
    public function setMarginLeft($marginLeft)
    {
        $this->marginLeft = $marginLeft;
    }

    /**
     * @return string
     */
    public function getMarginRight()
    {
        return $this->marginRight;
    }

    /**
     * @param string $marginRight
     */
    public function setMarginRight($marginRight)
    {
        $this->marginRight = $marginRight;
    }

    /**
     * @return string
     */
    public function getMarginTop()
    {
        return $this->marginTop;
    }

    /**
     * @param string $marginTop
     */
    public function setMarginTop($marginTop)
    {
        $this->marginTop = $marginTop;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }
}
