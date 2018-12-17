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

namespace SwagAboCommerce\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_plugin_swag_abo_commerce_articles")
 */
class Product extends ModelEntity
{
    /**
     * Unique identifier
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * The article id of the selected article.
     *
     * @var int
     *
     * @ORM\Column(name="article_id", type="integer", nullable=false)
     */
    private $articleId;

    /**
     * @var \Shopware\Models\Article\Article
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    private $article;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="SwagAboCommerce\Models\Price",
     *     mappedBy="aboArticle",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     */
    private $prices;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="exclusive", type="boolean", nullable=false)
     */
    private $exclusive = false;

    /**
     * @var string
     *
     * @ORM\Column(name="ordernumber", type="string", nullable=false)
     */
    private $ordernumber;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * Minimal duration in unit $durationUnit
     *
     * @var int
     *
     * @ORM\Column(name="min_duration", type="integer")
     */
    private $minDuration;

    /**
     * Maximum duration in unit $durationUnit
     *
     * @var int
     *
     * @ORM\Column(name="max_duration", type="integer")
     */
    private $maxDuration;

    /**
     * Unit of $durationUnit weeks/months etc.
     *
     * @var string
     *
     * @ORM\Column(name="duration_unit", type="string")
     */
    private $durationUnit;

    /**
     * Minimal duration in unit $durationUnit
     *
     * @var int
     *
     * @ORM\Column(name="min_delivery_interval", type="integer", nullable=false)
     */
    private $minDeliveryInterval;

    /**
     * Maximum duration in unit $deliveryIntervalUnit
     *
     * @var int
     *
     * @ORM\Column(name="max_delivery_interval", type="integer", nullable=false)
     */
    private $maxDeliveryInterval;

    /**
     * Unit of delivery in weeks/months etc.
     *
     * @var string
     *
     * @ORM\Column(name="delivery_interval_unit", type="string", nullable=false)
     */
    private $deliveryIntervalUnit;

    /**
     * Determines if subscription has no fixed runtime but is endless
     *
     * @var bool
     *
     * @ORM\Column(name="endless_subscription", type="boolean", nullable=false)
     */
    private $endlessSubscription = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="direct_termination", type="boolean", nullable=true)
     */
    private $directTermination = false;

    /**
     * @var string
     *
     * @ORM\Column(name="period_of_notice_interval", type="integer")
     */
    private $periodOfNoticeInterval;

    /**
     * @var int
     *
     * @ORM\Column(name="period_of_notice_unit", type="string")
     */
    private $periodOfNoticeUnit;

    /**
     * @var bool
     *
     * @ORM\Column(name="limited", type="boolean", nullable=false)
     */
    private $limited = false;

    /**
     * @var int
     *
     * @ORM\Column(name="max_Units_per_week", type="integer", nullable=false)
     */
    private $maxUnitsPerWeek;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->prices = new ArrayCollection();
    }

    /**
     * @return \Shopware\Models\Article\Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param \Shopware\Models\Article\Article $product
     *
     * @return \SwagAboCommerce\Models\Product
     */
    public function setArticle($product)
    {
        $this->article = $product;

        return $this;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $prices
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function setPrices($prices)
    {
        return $this->setOneToMany($prices, Price::class, 'prices', 'aboArticle');
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param bool $active
     *
     * @return \SwagAboCommerce\Models\Product
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;

        return $this;
    }

    /**
     * @return int
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string $description
     *
     * @return \SwagAboCommerce\Models\Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * @param bool $exclusive
     *
     * @return \SwagAboCommerce\Models\Product
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = (bool) $exclusive;

        return $this;
    }

    /**
     * @return bool
     */
    public function getExclusive()
    {
        return $this->exclusive;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $limited
     *
     * @return \SwagAboCommerce\Models\Product
     */
    public function setLimited($limited)
    {
        $this->limited = (bool) $limited;

        return $this;
    }

    /**
     * @return bool
     */
    public function getLimited()
    {
        return $this->limited;
    }

    /**
     * @param int $maxDeliveryInterval
     *
     * @return \SwagAboCommerce\Models\Product
     */
    public function setMaxDeliveryInterval($maxDeliveryInterval)
    {
        $this->maxDeliveryInterval = $maxDeliveryInterval;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxDeliveryInterval()
    {
        return $this->maxDeliveryInterval;
    }

    /**
     * @param int $maxDuration
     *
     * @return \SwagAboCommerce\Models\Product
     */
    public function setMaxDuration($maxDuration)
    {
        $this->maxDuration = $maxDuration;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxDuration()
    {
        return $this->maxDuration;
    }

    /**
     * @param int $minDeliveryInterval
     *
     * @return \SwagAboCommerce\Models\Product
     */
    public function setMinDeliveryInterval($minDeliveryInterval)
    {
        $this->minDeliveryInterval = $minDeliveryInterval;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinDeliveryInterval()
    {
        return $this->minDeliveryInterval;
    }

    /**
     * @param string $deliveryIntervalUnit
     *
     * @return \SwagAboCommerce\Models\Product
     */
    public function setDeliveryIntervalUnit($deliveryIntervalUnit)
    {
        $this->deliveryIntervalUnit = $deliveryIntervalUnit;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryIntervalUnit()
    {
        return $this->deliveryIntervalUnit;
    }

    /**
     * @param int $minDuration
     *
     * @return \SwagAboCommerce\Models\Product
     */
    public function setMinDuration($minDuration)
    {
        $this->minDuration = $minDuration;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinDuration()
    {
        return $this->minDuration;
    }

    /**
     * @param string $durationUnit
     *
     * @return \SwagAboCommerce\Models\Product
     */
    public function setDurationUnit($durationUnit)
    {
        $this->durationUnit = $durationUnit;

        return $this;
    }

    /**
     * @return string
     */
    public function getDurationUnit()
    {
        return $this->durationUnit;
    }

    /**
     * @param int $maxUnitsPerWeek
     *
     * @return \SwagAboCommerce\Models\Product
     */
    public function setMaxUnitsPerWeek($maxUnitsPerWeek)
    {
        $this->maxUnitsPerWeek = $maxUnitsPerWeek;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxUnitsPerWeek()
    {
        return $this->maxUnitsPerWeek;
    }

    /**
     * @param string $ordernumber
     *
     * @return \SwagAboCommerce\Models\Product
     */
    public function setOrdernumber($ordernumber)
    {
        $this->ordernumber = $ordernumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrdernumber()
    {
        return $this->ordernumber;
    }

    /**
     * @return bool
     */
    public function getEndlessSubscription()
    {
        return $this->endlessSubscription;
    }

    /**
     * @param bool $endlessSubscription
     */
    public function setEndlessSubscription($endlessSubscription)
    {
        $this->endlessSubscription = $endlessSubscription;
    }

    /**
     * @return string
     */
    public function getPeriodOfNoticeInterval()
    {
        return $this->periodOfNoticeInterval;
    }

    /**
     * @param string $periodOfNoticeInterval
     */
    public function setPeriodOfNoticeInterval($periodOfNoticeInterval)
    {
        $this->periodOfNoticeInterval = $periodOfNoticeInterval;
    }

    /**
     * @return int
     */
    public function getPeriodOfNoticeUnit()
    {
        return $this->periodOfNoticeUnit;
    }

    /**
     * @param int $periodOfNoticeUnit
     */
    public function setPeriodOfNoticeUnit($periodOfNoticeUnit)
    {
        $this->periodOfNoticeUnit = $periodOfNoticeUnit;
    }

    /**
     * @return bool
     */
    public function getDirectTermination()
    {
        return $this->directTermination;
    }

    /**
     * @param bool $directTermination
     */
    public function setDirectTermination($directTermination)
    {
        $this->directTermination = $directTermination;
    }
}
