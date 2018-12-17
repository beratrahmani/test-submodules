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

namespace SwagPromotion\Models;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Voucher\Voucher;

/**
 * @ORM\Table(name="s_plugin_promotion", indexes={
 *          @Index(name="promotion_repository", columns={"active", "valid_from", "valid_to"})
 *      }
 * )
 * @ORM\Entity()
 */
class Promotion extends ModelEntity
{
    /**
     * @var Voucher[]
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Voucher\Voucher")
     * @ORM\JoinColumn(name="voucher_id", referencedColumnName="id")
     */
    protected $voucher;
    /**
     * Primary Key - autoincrement value
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="rules", type="text", nullable=false)
     */
    private $rules;

    /**
     * @var string
     *
     * @ORM\Column(name="apply_rules", type="text", nullable=true)
     */
    private $applyRules;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", nullable=true)
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="detail_description", type="text", nullable=true)
     */
    private $detailDescription;

    /**
     * @var int
     *
     * @ORM\Column(name="max_usage", type="integer", nullable=true)
     */
    private $maxUsage;

    /**
     * @var int
     *
     * @ORM\Column(name="voucher_id", type="integer", nullable=true)
     */
    private $voucherId;

    /**
     * @var bool
     *
     * @ORM\Column(name="no_vouchers", type="boolean", nullable=false)
     */
    private $noVouchers;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=true)
     */
    private $validFrom = null;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="valid_to", type="datetime", nullable=true)
     */
    private $validTo = null;

    /**
     * @var string
     *
     * @ORM\Column(name="stack_mode", type="string", nullable=false)
     */
    private $stackMode;

    /**
     * @var float amount
     *
     * @ORM\Column(name="amount", type="float", nullable=true)
     */
    private $amount;

    /**
     * @var int step
     *
     * @ORM\Column(name="step", type="integer", nullable=true)
     */
    private $step;

    /**
     * @var int maxQuantity
     *
     * @ORM\Column(name="max_quantity", type="integer", nullable=true)
     */
    private $maxQuantity;

    /**
     * @var \Shopware\Models\Customer\Group[]
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinTable(name="s_plugin_promotion_customer_group",
     *      joinColumns={
     *          @ORM\JoinColumn(name="promotionID", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="groupID", referencedColumnName="id")
     *      }
     * )
     */
    private $customerGroups;

    /**
     * @var \Shopware\Models\Article\Article[]
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinTable(name="s_plugin_promotion_free_goods",
     *      joinColumns={
     *          @ORM\JoinColumn(name="promotionID", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *      }
     * )
     */
    private $freeGoodsArticle;

    /**
     * @var Promotion[]
     *
     * @ORM\ManyToMany(targetEntity="SwagPromotion\Models\Promotion")
     * @ORM\JoinTable(name="s_plugin_promotion_do_not_allow_later",
     *      joinColumns={
     *          @ORM\JoinColumn(name="promotionID", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="doNotAllowLaterID", referencedColumnName="id")
     *      }
     * )
     */
    private $doNotAllowLater;

    /**
     * @var Promotion[]
     *
     * @ORM\ManyToMany(targetEntity="SwagPromotion\Models\Promotion")
     * @ORM\JoinTable(name="s_plugin_promotion_do_not_run_after",
     *      joinColumns={
     *          @ORM\JoinColumn(name="promotionID", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="doNotRunAfterID", referencedColumnName="id")
     *      }
     * )
     */
    private $doNotRunAfter;

    /**
     * @var \Shopware\Models\Shop\Shop[]
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinTable(name="s_plugin_promotion_shop",
     *      joinColumns={
     *          @ORM\JoinColumn(name="promotionID", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="shopID", referencedColumnName="id")
     *      }
     * )
     */
    private $shops;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var bool
     *
     * @ORM\Column(name="exclusive", type="boolean", nullable=false)
     */
    private $exclusive;

    /**
     * @var bool
     *
     * @ORM\Column(name="shipping_free", type="boolean", nullable=true)
     */
    private $shippingFree;

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="integer", nullable=true)
     */
    private $priority;

    /**
     * @var bool
     *
     * @ORM\Column(name="stop_processing", type="boolean", nullable=false)
     */
    private $stopProcessing;

    /**
     * @var bool
     *
     * @ORM\Column(name="show_badge", type="boolean", nullable=false)
     */
    private $showBadge = true;

    /**
     * @var string
     *
     * @ORM\Column(name="badge_text", type="string", nullable=true)
     */
    private $badgeText;

    /**
     * @var bool
     *
     * @ORM\Column(name="apply_rules_first", type="boolean", nullable=false)
     */
    private $applyRulesFirst;

    /**
     * @var bool
     *
     * @ORM\Column(name="show_hint_in_basket", type="boolean", nullable=false)
     */
    private $showHintInBasket = true;

    /**
     * initialise the associations
     */
    public function __construct()
    {
        $this->customerGroups = new ArrayCollection();
        $this->freeGoodsArticle = new ArrayCollection();
        $this->doNotAllowLater = new ArrayCollection();
        $this->doNotRunAfter = new ArrayCollection();
        $this->shops = new ArrayCollection();
        $this->validFrom = new DateTime();
        $this->validTo = new DateTime();
        $this->voucher = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param string $rules
     *
     * @return $this
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @return string
     */
    public function getApplyRules()
    {
        return $this->applyRules;
    }

    /**
     * @param string $applyRules
     *
     * @return $this
     */
    public function setApplyRules($applyRules)
    {
        $this->applyRules = $applyRules;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     *
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;

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
    public function getDetailDescription()
    {
        return $this->detailDescription;
    }

    /**
     * @param string $detailDescription
     *
     * @return $this
     */
    public function setDetailDescription($detailDescription)
    {
        $this->detailDescription = $detailDescription;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxUsage()
    {
        return $this->maxUsage;
    }

    /**
     * @param int $maxUsage
     *
     * @return $this
     */
    public function setMaxUsage($maxUsage)
    {
        $this->maxUsage = $maxUsage;

        return $this;
    }

    /**
     * @return bool
     */
    public function getNoVouchers()
    {
        return $this->noVouchers;
    }

    /**
     * @param bool $noVouchers
     *
     * @return $this
     */
    public function setNoVouchers($noVouchers)
    {
        $this->noVouchers = $noVouchers;

        return $this;
    }

    /**
     * @return Voucher[]
     */
    public function getVoucher()
    {
        return $this->voucher;
    }

    /**
     * @param Voucher[] $voucher
     *
     * @return $this
     */
    public function setVoucher($voucher)
    {
        if (empty($voucher)) {
            $this->voucher = null;

            return $this;
        }

        return $this->setManyToOne($voucher, Voucher::class, 'voucher');
    }

    /**
     * @return DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @param DateTime $validFrom
     *
     * @return $this
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @param DateTime $validTo
     *
     * @return $this
     */
    public function setValidTo($validTo)
    {
        $this->validTo = $validTo;

        return $this;
    }

    /**
     * @return string
     */
    public function getStackMode()
    {
        return $this->stackMode;
    }

    /**
     * @param string $stackMode
     *
     * @return $this
     */
    public function setStackMode($stackMode)
    {
        $this->stackMode = $stackMode;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param int $step
     *
     * @return $this
     */
    public function setStep($step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxQuantity()
    {
        return $this->maxQuantity;
    }

    /**
     * @param int $maxQuantity
     *
     * @return $this
     */
    public function setMaxQuantity($maxQuantity)
    {
        $this->maxQuantity = $maxQuantity;

        return $this;
    }

    /**
     * @return \Shopware\Models\Customer\Group[]
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * @param \Shopware\Models\Customer\Group[] $customerGroups
     *
     * @return $this
     */
    public function setCustomerGroups($customerGroups)
    {
        $this->customerGroups = $customerGroups;

        return $this;
    }

    /**
     * @return \Shopware\Models\Article\Article[]
     */
    public function getFreeGoodsArticle()
    {
        return $this->freeGoodsArticle;
    }

    /**
     * @param \Shopware\Models\Article\Article[] $freeGoodsArticle
     *
     * @return $this
     */
    public function setFreeGoodsArticle($freeGoodsArticle)
    {
        $this->freeGoodsArticle = $freeGoodsArticle;

        return $this;
    }

    /**
     * @return Promotion[]
     */
    public function getDoNotAllowLater()
    {
        return $this->doNotAllowLater;
    }

    /**
     * @param Promotion[] $doNotAllowLater
     *
     * @return $this
     */
    public function setDoNotAllowLater($doNotAllowLater)
    {
        $this->doNotAllowLater = $doNotAllowLater;

        return $this;
    }

    /**
     * @return Promotion[]
     */
    public function getDoNotRunAfter()
    {
        return $this->doNotRunAfter;
    }

    /**
     * @param Promotion[] $doNotRunAfter
     *
     * @return $this
     */
    public function setDoNotRunAfter($doNotRunAfter)
    {
        $this->doNotRunAfter = $doNotRunAfter;

        return $this;
    }

    /**
     * @return \Shopware\Models\Shop\Shop[]
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param \Shopware\Models\Shop\Shop[] $shops
     *
     * @return $this
     */
    public function setShops($shops)
    {
        $this->shops = $shops;

        return $this;
    }

    /**
     * @return bool
     */
    public function getActive()
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
     * @return bool
     */
    public function getExclusive()
    {
        return $this->exclusive;
    }

    /**
     * @param bool $exclusive
     *
     * @return $this
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;

        return $this;
    }

    /**
     * @return bool
     */
    public function getShippingFree()
    {
        return $this->shippingFree;
    }

    /**
     * @param bool $shippingFree
     *
     * @return $this
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return bool
     */
    public function getStopProcessing()
    {
        return $this->stopProcessing;
    }

    /**
     * @param bool $stopProcessing
     *
     * @return $this
     */
    public function setStopProcessing($stopProcessing)
    {
        $this->stopProcessing = $stopProcessing;

        return $this;
    }

    /**
     * @return bool
     */
    public function getShowBadge()
    {
        return $this->showBadge;
    }

    /**
     * @param bool $showBadge
     *
     * @return $this
     */
    public function setShowBadge($showBadge)
    {
        $this->showBadge = $showBadge;

        return $this;
    }

    /**
     * @return string
     */
    public function getBadgeText()
    {
        return $this->badgeText;
    }

    /**
     * @param string $badgeText
     */
    public function setBadgeText($badgeText)
    {
        $this->badgeText = $badgeText;
    }

    /**
     * @return bool
     */
    public function getApplyRulesFirst()
    {
        return $this->applyRulesFirst;
    }

    /**
     * @param bool $applyRulesFirst
     */
    public function setApplyRulesFirst($applyRulesFirst)
    {
        $this->applyRulesFirst = $applyRulesFirst;
    }

    /**
     * @return bool
     */
    public function getShowHintInBasket()
    {
        return $this->showHintInBasket;
    }

    /**
     * @param bool $showHintInBasket
     */
    public function setShowHintInBasket($showHintInBasket)
    {
        $this->showHintInBasket = $showHintInBasket;
    }
}
