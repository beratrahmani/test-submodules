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
use Shopware\Models\Media\Media;

/**
 * Digital Publishing Content Banner
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 *
 * @ORM\Table(name="s_digital_publishing_content_banner")
 * @ORM\Entity(repositoryClass="Repository")
 */
class ContentBanner extends ModelEntity
{
    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Media\Media")
     * @ORM\JoinColumn(name="mediaId", referencedColumnName="id", nullable=true)
     */
    protected $media;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="SwagDigitalPublishing\Models\Layer",
     *     mappedBy="contentBanner",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    protected $layers;
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
     * @ORM\Column(type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", options={"default": "color"})
     */
    private $bgType;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $bgOrientation;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $bgMode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $bgColor;

    /**
     * @var int
     *
     * @ORM\Column(name="mediaId", type="integer", nullable=true)
     */
    private $mediaId;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->layers = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = null;

        $layers = [];

        foreach ($this->getLayers() as $layer) {
            $newLayer = clone $layer;
            $newLayer->setContentBanner($this);

            $layers[] = $newLayer;
        }

        $this->layers = $layers;
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
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param Media $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * @return ArrayCollection
     */
    public function getLayers()
    {
        return $this->layers;
    }

    /**
     * @param array $layers
     */
    public function setLayers($layers)
    {
        $this->setOneToMany($layers, Layer::class, 'layers', 'contentBanner');
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
    public function getBgType()
    {
        return $this->bgType;
    }

    /**
     * @param string $bgType
     */
    public function setBgType($bgType)
    {
        $this->bgType = $bgType;
    }

    /**
     * @return string
     */
    public function getBgOrientation()
    {
        return $this->bgOrientation;
    }

    /**
     * @param string $bgOrientation
     */
    public function setBgOrientation($bgOrientation)
    {
        $this->bgOrientation = $bgOrientation;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getBgMode()
    {
        return $this->bgMode;
    }

    /**
     * @param string $bgMode
     */
    public function setBgMode($bgMode)
    {
        $this->bgMode = $bgMode;
    }
}
