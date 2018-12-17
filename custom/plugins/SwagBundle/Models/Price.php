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

namespace SwagBundle\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Customer\Group;

/**
 * Price model of the bundle plugin.
 * The price model contains the definition of a single bundle price for a single customer group.
 * Each customer group, which has a price and added in the SwagBundle\Models\Bundle::customerGroups property,
 * can buy/see the bundle in the store front.
 * The price contains only the assigned bundle, customer group and the custom inserted price.
 *
 * @category Shopware
 *
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity
 * @ORM\Table(name="s_articles_bundles_prices")
 */
class Price extends ModelEntity
{
    /**
     * OWNING SIDE
     * The $bundle property contains the assigned instance of \SwagBundle\Models\Bundle.
     *
     * @ORM\ManyToOne(targetEntity="SwagBundle\Models\Bundle", inversedBy="prices")
     * @ORM\JoinColumn(name="bundle_id", referencedColumnName="id")
     *
     * @var Bundle
     */
    protected $bundle;

    /**
     * Contains the defined customer group model instance (\Shopware\Models\Customer\Group) on which the price
     * defined.
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id")
     *
     * @var Group
     */
    protected $customerGroup;
    /**
     * Unique identifier for a single bundle product
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Identifier for the assigned bundle.
     * Used as foreign key for the bundle association.
     * Has no getter and setter.
     * Only defined to have access on the bundle id in queries without joining the s_articles_bundles.
     *
     * @ORM\Column(name="bundle_id", type="integer", nullable=false)
     *
     * @var int
     */
    private $bundleId;
    /**
     * Identifier for the assigned customer group of the defined price.
     * Used as foreign key for the customer group association.
     * Has no getter and setter.
     * Only defined to have access on the customer group key in queries without joining the s_core_customergroups.
     *
     * @ORM\Column(name="customer_group_id", type="integer", nullable=false)
     *
     * @var int
     */
    private $customerGroupId;

    /**
     * The defined custom bundle price.
     * Defined over the backend module.
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     *
     * @var float
     */
    private $price;

    /**
     * Class property which contains the price which has to been displayed in the store front.
     *
     * @var float
     */
    private $displayPrice;

    /**
     * Class property which contains the net price.
     *
     * @var float
     */
    private $netPrice;

    /**
     * Class property which contains the percentage value for the bundle price.
     *
     * @var int
     */
    private $percentage;

    /**
     * Class property which contains the gross price.
     *
     * @var float
     */
    private $grossPrice;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Bundle
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @param Bundle $bundle
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;
    }

    /**
     * @return Group
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param Group $customerGroup
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;
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
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getDisplayPrice()
    {
        return $this->displayPrice;
    }

    /**
     * @param float $displayPrice
     */
    public function setDisplayPrice($displayPrice)
    {
        $this->displayPrice = $displayPrice;
    }

    /**
     * @return float
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @param float $netPrice
     */
    public function setNetPrice($netPrice)
    {
        $this->netPrice = $netPrice;
    }

    /**
     * @return int
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * @param int $percentage
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;
    }

    /**
     * @return float
     */
    public function getGrossPrice()
    {
        return $this->grossPrice;
    }

    /**
     * @param float $grossPrice
     */
    public function setGrossPrice($grossPrice)
    {
        $this->grossPrice = $grossPrice;
    }
}
