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

namespace SwagLiveShopping\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 *
 * @ORM\Entity
 * @ORM\Table(name="s_articles_live_prices")
 */
class Price extends ModelEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="live_shopping_id", type="integer", nullable=true)
     */
    protected $liveShoppingId;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     */
    protected $price;

    /**
     * @var float
     *
     * @ORM\Column(name="endprice", type="float", nullable=false)
     */
    protected $endPrice;

    /**
     * @ORM\ManyToOne(targetEntity="SwagLiveShopping\Models\LiveShopping", inversedBy="prices")
     * @ORM\JoinColumn(name="live_shopping_id", referencedColumnName="id")
     *
     * @var LiveShopping
     */
    protected $liveShopping;

    /**
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id")
     *
     * @var \Shopware\Models\Customer\Group
     */
    protected $customerGroup;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Price
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getLiveShoppingId()
    {
        return $this->liveShoppingId;
    }

    /**
     * @param int $liveShoppingId
     *
     * @return Price
     */
    public function setLiveShoppingId($liveShoppingId)
    {
        $this->liveShoppingId = $liveShoppingId;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return Price
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getEndPrice()
    {
        return $this->endPrice;
    }

    /**
     * @param float $endPrice
     *
     * @return Price
     */
    public function setEndPrice($endPrice)
    {
        $this->endPrice = $endPrice;

        return $this;
    }

    /**
     * @return LiveShopping
     */
    public function getLiveShopping()
    {
        return $this->liveShopping;
    }

    /**
     * @param LiveShopping $liveShopping
     */
    public function setLiveShopping($liveShopping)
    {
        $this->liveShopping = $liveShopping;
    }

    /**
     * @return \Shopware\Models\Customer\Group
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param \Shopware\Models\Customer\Group $customerGroup
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }
}
