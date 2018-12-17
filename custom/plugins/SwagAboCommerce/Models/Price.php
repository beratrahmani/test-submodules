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

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_plugin_swag_abo_commerce_prices")
 */
class Price extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * @var \SwagAboCommerce\Models\Product
     *
     * @ORM\ManyToOne(targetEntity="\SwagAboCommerce\Models\Product", inversedBy="prices")
     * @ORM\JoinColumn(name="abo_article_id", referencedColumnName="id")
     */
    private $aboArticle;

    /**
     * @var int
     *
     * @ORM\Column(name="abo_article_id", type="integer", nullable=true)
     */
    private $aboArticleId;

    /**
     * @var \Shopware\Models\Customer\Group
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id")
     */
    private $customerGroup;

    /**
     * @var int
     *
     * @ORM\Column(name="customer_group_id", type="integer", nullable=true)
     */
    private $customerGroupId;

    /**
     * @var float
     *
     * @ORM\Column(name="discount_absolute", type="float", nullable=false)
     */
    private $discountAbsolute;

    /**
     * @var float
     *
     * @ORM\Column(name="discount_percent", type="float", nullable=false)
     */
    private $discountPercent;

    /**
     * @var int
     *
     * @ORM\Column(name="duration_from", type="integer", nullable=false)
     */
    private $durationFrom;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \SwagAboCommerce\Models\Product $aboProduct
     *
     * @return Price
     */
    public function setAboArticle($aboProduct)
    {
        $this->aboArticle = $aboProduct;

        return $this;
    }

    /**
     * @return \SwagAboCommerce\Models\Product
     */
    public function getAboArticle()
    {
        return $this->aboArticle;
    }

    /**
     * @param int $aboProductId
     *
     * @return Price
     */
    public function setAboArticleId($aboProductId)
    {
        $this->aboArticleId = $aboProductId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAboArticleId()
    {
        return $this->aboArticleId;
    }

    /**
     * @param \Shopware\Models\Customer\Group $customerGroup
     *
     * @return Price
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;

        return $this;
    }

    /**
     * @return \Shopware\Models\Customer\Group
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param int $customerGroupId
     *
     * @return Price
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->customerGroupId = $customerGroupId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->customerGroupId;
    }

    /**
     * @param float $discountAbsolute
     *
     * @return Price
     */
    public function setDiscountAbsolute($discountAbsolute)
    {
        $this->discountAbsolute = $discountAbsolute;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountAbsolute()
    {
        return $this->discountAbsolute;
    }

    /**
     * @param float $discountPercent
     *
     * @return Price
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->discountPercent = $discountPercent;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }

    /**
     * @param int $durationFrom
     *
     * @return Price
     */
    public function setDurationFrom($durationFrom)
    {
        $this->durationFrom = $durationFrom;

        return $this;
    }

    /**
     * @return int
     */
    public function getDurationFrom()
    {
        return $this->durationFrom;
    }
}
