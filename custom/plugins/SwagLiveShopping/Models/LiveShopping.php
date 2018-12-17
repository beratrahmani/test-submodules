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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Group;
use Shopware\Models\Shop\Shop;
use SwagLiveShopping\Components\LiveShoppingInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Shopware LiveShopping model
 *
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_articles_lives")
 *
 * @Assert\Callback(methods={"validateLiveShopping"})
 */
class LiveShopping extends ModelEntity
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
     * @ORM\Column(name="article_id", type="integer", nullable=true)
     */
    protected $articleId;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer", nullable=true)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    protected $name = '';

    /**
     * @var int
     *
     * @ORM\Column(name="active", type="integer", nullable=false)
     */
    protected $active = false;

    /**
     * @var string
     *
     * @ORM\Column(name="order_number", type="string", nullable=true)
     */
    protected $number;

    /**
     * @var int
     *
     * @ORM\Column(name="max_quantity_enable", type="integer", nullable=false)
     */
    protected $limited = false;

    /**
     * @var int
     *
     * @ORM\Column(name="max_quantity", type="integer", nullable=false)
     */
    protected $quantity = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="max_purchase", type="integer", nullable=false)
     */
    protected $purchase = 1;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=true)
     */
    protected $validFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="valid_to", type="datetime", nullable=true)
     */
    protected $validTo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datum", type="datetime", nullable=true)
     */
    protected $created = 'now';

    /**
     * @var int
     *
     * @ORM\Column(name="sells", type="integer", nullable=false)
     */
    protected $sells = 0;

    /**
     * @ORM\OneToMany(targetEntity="SwagLiveShopping\Models\Price", mappedBy="liveShopping", cascade={"persist"}, orphanRemoval=true)
     *
     * @var ArrayCollection
     */
    protected $prices;

    /**
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     *
     * @var Article
     */
    protected $article;

    /**
     * The $customerGroups property contains an offset of \Shopware\Models\Customer\Group.
     * All defined customer groups can buy the defined live shopping product.
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinTable(name="s_articles_live_customer_groups",
     *      joinColumns={
     *          @ORM\JoinColumn(name="live_shopping_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id")
     *      }
     * )
     *
     * @var ArrayCollection|Group[]
     */
    protected $customerGroups;

    /**
     * INVERSE SIDE
     * The $limitedVariants property contains an offset of \Shopware\Models\Article\Detail.
     * If the live shopping created on a configurator product, the bundle only displayed in the store
     * front if the user select one of the variants of this collection.
     * Otherwise the live shopping product will be hidden.
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Detail")
     * @ORM\JoinTable(name="s_articles_live_stint",
     *      joinColumns={
     *          @ORM\JoinColumn(name="live_shopping_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="article_detail_id", referencedColumnName="id")
     *      }
     * )
     *
     * @var ArrayCollection|Detail[]
     */
    protected $limitedVariants;

    /**
     * INVERSE SIDE
     * The $shops property contains an offset of \Shopware\Models\Shop\Shop.
     * The live shopping product is only displayed in the sub shops which are defined in this array.
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinTable(name="s_articles_live_shoprelations",
     *      joinColumns={
     *          @ORM\JoinColumn(name="live_shopping_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     *      }
     * )
     *
     * @var ArrayCollection|Shop[]
     */
    protected $shops;

    /**
     * Current price of the live shopping product.
     *
     * The current price property is an class property which
     * calculates from the Shopware_Components_LiveShopping class.
     * This property contains the numeric value of the current price
     * of the live shopping product.
     *
     * @var float
     */
    protected $currentPrice;

    /**
     * Class property.
     *
     * This property is only used in the store front.
     * The property contains updated live shopping prices for the current frontend session.
     * The updated prices based on the customer group, selected shipping address and customer login.
     *
     * @var ArrayCollection
     */
    protected $updatedPrices;

    /**
     * Class property.
     *
     * This property is only used in the store front.
     * The property contains the Reference Price of the Liveshopping price.
     *
     * @var float
     */
    protected $referenceUnitPrice;

    /**
     * Class constructor, initials the array collection of this model.
     */
    public function __construct()
    {
        $this->shops = new ArrayCollection();
        $this->limitedVariants = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->updatedPrices = new ArrayCollection();
        $this->customerGroups = new ArrayCollection();
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
     *
     * @return LiveShopping
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return LiveShopping
     */
    public function setType($type)
    {
        $this->type = $type;

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
     * @return LiveShopping
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool|int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return LiveShopping
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @param \DateTime|string $validFrom
     *
     * @return LiveShopping
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;

        if (!($validFrom instanceof \DateTime) && strlen($validFrom) > 0) {
            $this->validFrom = new \DateTime($validFrom);
        }

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @param \DateTime|string $validTo
     *
     * @return LiveShopping
     */
    public function setValidTo($validTo)
    {
        $this->validTo = $validTo;

        if (!($validTo instanceof \DateTime) && strlen($validTo) > 0) {
            $this->validTo = new \DateTime($validTo);
        }

        return $this;
    }

    /**
     * @return \DateTime|string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime|string $created
     *
     * @return LiveShopping
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return int
     */
    public function getSells()
    {
        return $this->sells;
    }

    /**
     * @param int $sells
     *
     * @return LiveShopping
     */
    public function setSells($sells)
    {
        $this->sells = $sells;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param ArrayCollection $prices
     *
     * @return self
     */
    public function setPrices($prices)
    {
        $this->setOneToMany($prices, Price::class, 'prices', 'liveShopping');

        return $this;
    }

    /**
     * @return ArrayCollection|Shop[]
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param ArrayCollection|Shop[] $shops
     */
    public function setShops($shops)
    {
        $this->shops = $shops;
    }

    /**
     * @return ArrayCollection|Detail[]
     */
    public function getLimitedVariants()
    {
        return $this->limitedVariants;
    }

    /**
     * @param ArrayCollection|Detail[] $limitedVariants
     */
    public function setLimitedVariants($limitedVariants)
    {
        $this->limitedVariants = $limitedVariants;
    }

    /**
     * @return ArrayCollection|Group[]
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * @param ArrayCollection|Group[] $customerGroups
     */
    public function setCustomerGroups($customerGroups)
    {
        $this->customerGroups = $customerGroups;
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
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @param int $limited
     */
    public function setLimited($limited)
    {
        $this->limited = $limited;
    }

    /**
     * @return int
     */
    public function getLimited()
    {
        return $this->limited;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $purchase
     */
    public function setPurchase($purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * @return int
     */
    public function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param Article $product
     */
    public function setArticle($product)
    {
        $this->article = $product;
    }

    /**
     * Time difference calculation.
     *
     * Returns an instance of the \DateInterval class which contains the difference between the valid from
     * and valid to date of this class.
     * In case that the valid from or valid to date isn't an instance of \DateTime the function returns false
     *
     * @return \DateInterval|bool
     */
    public function getTimeDifference()
    {
        try {
            return $this->getDateDifference($this->getValidFrom(), $this->getValidTo());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Minute difference calculation.
     *
     * Returns the numeric value of the minute difference between the valid from and valid to date.
     * In case that the getTimeDifference function returns false, this function returns false, too.
     *
     * @return bool|int
     */
    public function getMinuteDifference()
    {
        return $this->getTotalMinutesOfDateInterval(
            $this->getTimeDifference()
        );
    }

    /**
     * Price calculation for the difference between start and end price.
     *
     * This function returns the difference between the start and end price
     * of the first price record of the prices collection.
     * In case that the prices collection has no record, this function
     * returns false.
     *
     * @return float|bool
     */
    public function getPriceDifference()
    {
        $price = $this->getPrices()->first();

        if (!$price instanceof Price) {
            return false;
        }

        if ($this->type === LiveShoppingInterface::SURCHARGE_TYPE) {
            return $price->getEndPrice() - $price->getPrice();
        }

        return $price->getPrice() - $price->getEndPrice();
    }

    /**
     * Per minute calculation.
     *
     * This function returns the discount/surcharge value per minute
     * for live shopping products which has configured as "discount per minute" or
     * as "surcharge per minute". The per minute value is used for the frontend price calculation
     * for each live shopping product. This calculation is based on the valid from and valid to
     * dates.
     */
    public function getPerMinuteValue()
    {
        $timeDifference = $this->getMinuteDifference();

        $priceDiffence = $this->getPriceDifference();

        if ($timeDifference === false || $priceDiffence === false) {
            return false;
        }

        return $priceDiffence / $timeDifference;
    }

    /**
     * Returns the total minutes of the passed interval.
     *
     * This function is used to calculate the current price of a live shopping product.
     *
     * @param \DateInterval $interval
     *
     * @return bool|int
     */
    public function getTotalMinutesOfDateInterval($interval)
    {
        if (!$interval instanceof \DateInterval) {
            return false;
        }

        $dayMinutes = $interval->days * 24 * 60;

        $hourMinutes = $interval->h * 60;

        return $interval->i + $dayMinutes + $hourMinutes;
    }

    /**
     * Returns the time difference of the passed dates.
     *
     * This function returns an instance of the \DateInterval class which contains the
     * difference between the two passed date objects.
     *
     * <pre>
     * Example:
     *  DateInterval Object
     *  (
     *      [y] => 0
     *      [m] => 0
     *      [d] => 0
     *      [h] => 12
     *      [i] => 40
     *      [s] => 0
     *      [invert] => 0
     *      [days] => 0
     *  )
     * </pre>
     *
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return bool|\DateInterval
     */
    public function getDateDifference($from, $to)
    {
        if (!($from instanceof \DateTime || !($to instanceof \DateTime))) {
            return false;
        }

        return $from->diff($to);
    }

    /**
     * Returns the expired minutes.
     *
     * This function returns the total minutes which has expired
     * since the valid from date to the passed date.
     *
     * @param string $date
     *
     * @return bool|\DateInterval
     */
    public function getExpiredDateInterval($date = 'now')
    {
        if (!$date instanceof \DateTime && strlen($date) > 0) {
            $date = new \DateTime($date);
        } elseif (!$date instanceof \DateTime) {
            $date = new \DateTime();
        }

        return $this->getDateDifference($this->getValidFrom(), $date);
    }

    /**
     * Returns the remaining time.
     *
     * This function is used to calculate the remaining time of a single live shopping product.
     *
     * @param string $date
     *
     * @return bool|\DateInterval
     */
    public function getRemainingDateInterval($date = 'now')
    {
        if (!$date instanceof \DateTime && strlen($date) > 0) {
            $date = new \DateTime($date);
        } elseif (!$date instanceof \DateTime) {
            $date = new \DateTime();
        }
        if ($date > $this->getValidTo()) {
            return false;
        }

        return $this->getDateDifference($date, $this->getValidTo());
    }

    /**
     * @return float
     */
    public function getCurrentPrice()
    {
        return $this->currentPrice;
    }

    /**
     * @param float $currentPrice
     */
    public function setCurrentPrice($currentPrice)
    {
        $this->currentPrice = $currentPrice;
    }

    /**
     * @return ArrayCollection
     */
    public function getUpdatedPrices()
    {
        if (!$this->updatedPrices instanceof ArrayCollection) {
            if (is_array($this->updatedPrices)) {
                $this->updatedPrices = new ArrayCollection($this->updatedPrices);
            } else {
                $this->updatedPrices = new ArrayCollection();
            }
        }

        return $this->updatedPrices;
    }

    /**
     * @param ArrayCollection $updatedPrices
     */
    public function setUpdatedPrices($updatedPrices)
    {
        $this->updatedPrices = $updatedPrices;
    }

    /**
     * @param float $givenReferenceUnitPrice
     */
    public function setReferenceUnitPrice($givenReferenceUnitPrice)
    {
        $this->referenceUnitPrice = $givenReferenceUnitPrice;
    }

    /**
     * @return float
     */
    public function getReferenceUnitPrice()
    {
        return $this->referenceUnitPrice;
    }
}
