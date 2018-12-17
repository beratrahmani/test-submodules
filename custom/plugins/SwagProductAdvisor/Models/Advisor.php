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

namespace SwagProductAdvisor\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Shop\Shop;

/**
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_plugin_product_advisor_advisor")
 */
class Advisor extends ModelEntity
{
    /**
     * @var \Shopware\Models\Media\Media
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Media\Media")
     * @ORM\JoinColumn(name="teaser_banner_id", referencedColumnName="id")
     */
    protected $teaserBanner;

    /**
     * @var \Shopware\Models\ProductStream\ProductStream
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\ProductStream\ProductStream")
     * @ORM\JoinColumn(name="stream_id", referencedColumnName="id")
     */
    protected $stream;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(
     *     targetEntity="SwagProductAdvisor\Models\Question",
     *     mappedBy="advisor",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    protected $questions;
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="stream_id", type="integer")
     */
    private $streamId;

    /**
     * @var int
     * @ORM\Column(name="teaser_banner_id", type="integer", nullable=true)
     */
    private $teaserBannerId;

    /**
     * @var bool
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(name="info_link_text", type="string", nullable=true)
     */
    private $infoLinkText;

    /**
     * @var string
     * @ORM\Column(name="button_text", type="string", nullable=true)
     */
    private $buttonText;

    /**
     * @var string
     * @ORM\Column(name="remaining_posts_title", type="string", nullable=true)
     */
    private $remainingPostsTitle;

    /**
     * @var string
     * @ORM\Column(name="listing_title_filtered", type="string", nullable=true)
     */
    private $listingTitleFiltered;

    /**
     * @var bool
     * @ORM\Column(name="highlight_top_hit", type="boolean")
     */
    private $highlightTopHit;

    /**
     * @var string
     * @ORM\Column(name="top_hit_title", type="string", nullable=true)
     */
    private $topHitTitle;

    /**
     * @var int
     * @ORM\Column(name="min_matching_attributes", type="integer", nullable=true)
     */
    private $minMatchingAttributes;

    /**
     * Listing,
     * Listing with highlighted hits,
     * Listing with highlighted hits and missing attributes
     *
     * @var string
     * @ORM\Column(name="listing_layout", type="string", nullable=true)
     */
    private $listingLayout;

    /**
     * wizard_mode
     * sidebar_mode
     *
     * @var string
     * @ORM\Column(name="mode", type="string", nullable=true)
     */
    private $mode;

    /**
     * price_sort_ASC
     * price_sort_DESC
     *
     * @var string
     * @ORM\Column(name="last_listing_sort", type="string", nullable=true)
     */
    private $lastListingSort;

    /**
     * Advisor constructor.
     */
    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    /**
     * Advisor Clone
     */
    public function __clone()
    {
        $this->id = null;

        $questions = [];
        foreach ($this->questions as $value) {
            /** @var Question $question */
            $question = clone $value;
            $question->setAdvisor($this);
            $questions[] = $question;
        }

        $this->questions = new ArrayCollection($questions);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
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
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getInfoLinkText()
    {
        return $this->infoLinkText;
    }

    /**
     * @param string $infoLinkText
     *
     * @return $this
     */
    public function setInfoLinkText($infoLinkText)
    {
        $this->infoLinkText = $infoLinkText;

        return $this;
    }

    /**
     * @return string
     */
    public function getButtonText()
    {
        return $this->buttonText;
    }

    /**
     * @param string $buttonText
     *
     * @return $this
     */
    public function setButtonText($buttonText)
    {
        $this->buttonText = $buttonText;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingTitleFiltered()
    {
        return $this->listingTitleFiltered;
    }

    /**
     * @param string $listingTitleFiltered
     *
     * @return $this
     */
    public function setListingTitleFiltered($listingTitleFiltered)
    {
        $this->listingTitleFiltered = $listingTitleFiltered;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHighlightTopHit()
    {
        return $this->highlightTopHit;
    }

    /**
     * @param bool $highlightTopHit
     *
     * @return $this
     */
    public function setHighlightTopHit($highlightTopHit)
    {
        $this->highlightTopHit = $highlightTopHit;

        return $this;
    }

    /**
     * @return string
     */
    public function getTopHitTitle()
    {
        return $this->topHitTitle;
    }

    /**
     * @param string $topHitTitle
     *
     * @return $this
     */
    public function setTopHitTitle($topHitTitle)
    {
        $this->topHitTitle = $topHitTitle;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinMatchingAttributes()
    {
        return $this->minMatchingAttributes;
    }

    /**
     * @param int $minMatchingAttributes
     *
     * @return $this
     */
    public function setMinMatchingAttributes($minMatchingAttributes)
    {
        $this->minMatchingAttributes = $minMatchingAttributes;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingLayout()
    {
        return $this->listingLayout;
    }

    /**
     * @param string $listingLayout
     *
     * @return $this
     */
    public function setListingLayout($listingLayout)
    {
        $this->listingLayout = $listingLayout;

        return $this;
    }

    /**
     * @return \Shopware\Models\Media\Media
     */
    public function getTeaserBanner()
    {
        return $this->teaserBanner;
    }

    /**
     * @param \Shopware\Models\Media\Media $teaserBanner
     *
     * @return $this
     */
    public function setTeaserBanner($teaserBanner)
    {
        $this->teaserBanner = $teaserBanner;

        return $this;
    }

    /**
     * @return \Shopware\Models\ProductStream\ProductStream
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @param \Shopware\Models\ProductStream\ProductStream $stream
     *
     * @return $this
     */
    public function setStream($stream)
    {
        $this->stream = $stream;

        return $this;
    }

    /**
     * @return \Doctrine\ORM\PersistentCollection
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @param array $questions
     *
     * @return $this
     */
    public function setQuestions($questions)
    {
        return $this->setOneToMany(
            $questions,
            Question::class,
            'questions',
            'advisor'
        );
    }

    /**
     * @param Question $question
     *
     * @return $this
     */
    public function addQuestion($question)
    {
        $this->questions->add($question);

        return $this;
    }

    /**
     * @param Shop $shop
     *
     * @return $this
     */
    public function addShop(Shop $shop)
    {
        if (!$this->shops->contains($shop)) {
            $this->shops->add($shop);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getLastListingSort()
    {
        return $this->lastListingSort;
    }

    /**
     * @param string $lastListingSort
     *
     * @return $this
     */
    public function setLastListingSort($lastListingSort)
    {
        $this->lastListingSort = $lastListingSort;

        return $this;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     *
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return string
     */
    public function getRemainingPostsTitle()
    {
        return $this->remainingPostsTitle;
    }

    /**
     * @param string $remainingPostsTitle
     *
     * @return $this
     */
    public function setRemainingPostsTitle($remainingPostsTitle)
    {
        $this->remainingPostsTitle = $remainingPostsTitle;

        return $this;
    }
}
