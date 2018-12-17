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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Group;
use SwagBundle\Models\Article as BundleProduct;

/**
 * Shopware Bundle Model
 * Contains the definition of a single shopware product bundle resource.
 *
 * @category Shopware
 *
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_articles_bundles")
 */
class Bundle extends ModelEntity
{
    /**
     * The order number of the dynamic discount. Used for the frontend basket.
     *
     * @ORM\Column(name="ordernumber", type="string", length=255, nullable=false)
     *
     * @var string
     */
    protected $number;

    /**
     * The $article property is an association property. This
     * property contains the Shopware\Models\Article\Article model
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *
     * @var Article
     */
    protected $article;

    /**
     * The $customerGroups property contains an offset of \Shopware\Models\Customer\Group.
     * All defined customer groups can buy the defined bundle.
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinTable(name="s_articles_bundles_customergroups",
     *      joinColumns={
     *          @ORM\JoinColumn(name="bundle_id", referencedColumnName="id")
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
     * The $articles property contains all assigned products of the defined bundle.
     * The array collection contains an offset of \SwagBundle\Models\Article objects.
     * This bundle product models contains a reference to the assigned \Shopware\Models\Article\Detail
     * instance in the $articleDetail property.
     *
     * @ORM\OneToMany(targetEntity="SwagBundle\Models\Article", mappedBy="bundle", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     *
     * @var ArrayCollection|BundleProduct[]
     */
    protected $articles;

    /**
     * INVERSE SIDE
     * The $limitedDetails property contains an offset of \Shopware\Models\Article\Detail.
     * If the bundle created on a configurator product, the bundle only displayed in the store
     * front if the user select one of the variants of this collection.
     * Otherwise the bundle will be hidden.
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Detail")
     * @ORM\JoinTable(name="s_articles_bundles_stint",
     *      joinColumns={
     *          @ORM\JoinColumn(name="bundle_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="article_detail_id", referencedColumnName="id")
     *      }
     * )
     *
     * @var ArrayCollection|Detail[]
     */
    protected $limitedDetails;

    /**
     * INVERSE SIDE
     * The $prices property contains the defined prices of the bundle.
     * The bundle has one price per customer group of the shop.
     *
     * @ORM\OneToMany(targetEntity="SwagBundle\Models\Price", mappedBy="bundle", orphanRemoval=true, cascade={"persist"})
     *
     * @var ArrayCollection|Price[]
     */
    protected $prices;

    /**
     * OWNING SIDE
     * Contains the selected tax which can be defined over the backend module.
     * Used for the bundle prices if the discount type is set to "absolute".
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Tax\Tax")
     * @ORM\JoinColumn(name="taxID", referencedColumnName="id")
     *
     * @var \Shopware\Models\Tax\Tax
     */
    protected $tax;
    /**
     * Unique identifier for a single bundle
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Custom name for the bundle which displayed in the backend module as bundle definition
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $name;

    /**
     * flag whether the name of the bundle is shown in the frontend or not
     *
     * @ORM\Column(name= "show_name", type="boolean", nullable=false)
     *
     * @var bool
     */
    private $showName;

    /**
     * The type property defines which typ of bundle are defined.
     * <code>
     * Valid types:
     *   1 => standard type (discount)
     *   2 => cross selling (checkboxes)
     * <code>
     *
     * @ORM\Column(name="bundle_type", type="integer", nullable=false)
     *
     * @var int
     */
    private $type;

    /**
     * The id of the assigned product on which the bundle created.
     * Used as foreign key for the product association.
     * Has no getter and setter. Only defined to have access on the product id in queries without joining the s_articles.
     *
     * @ORM\Column(name="articleID", type="integer", nullable=false)
     *
     * @var int
     */
    private $articleId;

    /**
     * Active flag for the bundle. The bundle only displayed in the shop front if the active flag is set to true.
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     *
     * @var bool
     */
    private $active;

    /**
     * The discount type used for the price calculation of the bundle.
     *
     * @ORM\Column(name="rab_type", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $discountType;
    /**
     * The description of the bundle.
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $description;

    /**
     * The id of the assigned tax which used for the discount.
     * Can be defined over the backend module.
     * Used as foreign key for the tax association.
     * Has no getter and setter. Only defined to have access on the tax id in queries without joining the s_core_taxes.
     *
     * @ORM\Column(name="taxID", type="integer", nullable=true)
     *
     * @var int
     */
    private $taxId;

    /**
     * Shows the position of a bundle in the detail page.
     *
     * @ORM\Column(name="bundle_position", type="integer", nullable=false)
     *
     * @var int
     */
    private $position;

    /**
     * Flag if the bundle should be displayed on every product, that is part of the bundle.
     *
     * @ORM\Column(name="display_global", type="boolean", nullable=false)
     *
     * @var bool
     */
    private $displayGlobal;

    /**
     * decides if delivery time is shown and how
     *
     * @ORM\Column(name="display_delivery", type="integer", nullable=true)
     *
     * @var int
     */
    private $displayDelivery;

    /**
     * Flag if the quantity of the bundle is limited.
     *
     * @ORM\Column(name="max_quantity_enable", type="boolean", nullable=false)
     *
     * @var bool
     */
    private $limited;

    /**
     * If the $limited flag is set to true, the $quantity property contains the value for the limitation.
     *
     * @ORM\Column(name="max_quantity", type="integer", nullable=false)
     *
     * @var int
     */
    private $quantity;

    /**
     * The valid from and valid to property allows a time control for the bundle.
     * If the valid from property is set, the bundle will be displayed in the shop front, after crossing the valid from date.
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $validFrom;

    /**
     * The valid from and valid to property allows a time control for the bundle.
     * If the valid to property is set, the bundle will be hidden in the shop front, after crossing the valid to date.
     *
     * @ORM\Column(name="valid_to", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $validTo;

    /**
     * Contains the creation date of the bundle
     *
     * @ORM\Column(name="datum", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $created;

    /**
     * Counter how many times the bundle sold.
     *
     * @ORM\Column(name="sells", type="integer", nullable=false)
     *
     * @var int
     */
    private $sells;

    /**
     * Class property which contains the discount data for the bundle
     *
     * @var array
     */
    private $discount;

    /**
     * Class property which contains the price for the current customer group
     *
     * @var Price
     */
    private $currentPrice;

    /**
     * Class property which contains the net and gross total prices for the bundle.
     *
     * @var array
     */
    private $totalPrice;

    /**
     * Class property which contains a flag if all configurator product in the bundle are configured.
     *
     * @var bool
     */
    private $allConfigured = true;

    /**
     * Class property which contains the configuration for the products.
     *
     * @var array
     */
    private $productData = [];

    /**
     * @var ArrayCollection
     */
    private $updatedPrices;

    /**
     * @var array a product
     */
    private $longestShippingTimeProduct;

    /**
     * Class constructor. Initials all objects of this class, like ArrayCollections and DateTimes
     */
    public function __construct()
    {
        $this->limitedDetails = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->customerGroups = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->updatedPrices = new ArrayCollection();
    }

    /**
     * Unique identifier.
     *
     * Returns the unique identifier of this model.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Active flag.
     * Used to activate and deactive a single bundle, to hide
     * the bundle in the frontend without deleting the bundle.
     *
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Active flag.
     * Used to activate and deactive a single bundle, to hide
     * the bundle in the frontend without deleting the bundle.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Date Range validation field.
     * Used to shrink the bundle display in the store front
     * to a specified time.
     *
     * @param \DateTime|string $validFrom
     */
    public function setValidFrom($validFrom = 'now')
    {
        $this->validFrom = $validFrom;

        if (!($validFrom instanceof \DateTime) && strlen($validFrom) > 0) {
            $this->validFrom = new \DateTime($validFrom);
        }
    }

    /**
     * Date Range validation field.
     * Used to shrink the bundle display in the store front
     * to a specified time.
     *
     * @return \DateTime|null
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * Date Range validation field.
     * Used to shrink the bundle display in the store front
     * to a specified time.
     *
     * @param \DateTime|string $validTo
     */
    public function setValidTo($validTo = 'now')
    {
        $this->validTo = $validTo;

        if (!($validTo instanceof \DateTime) && strlen($validTo) > 0) {
            $this->validTo = new \DateTime($validTo);
        }
    }

    /**
     * Date Range validation field.
     * Used to shrink the bundle display in the store front
     * to a specified time.
     *
     * @return \DateTime|null
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * Creation date.
     * The create date of the bundle.
     *
     * @param \DateTime|string $created
     */
    public function setCreated($created = 'now')
    {
        $this->created = $created;

        if (!($created instanceof \DateTime) && strlen($created) > 0) {
            $this->created = new \DateTime($created);
        }
    }

    /**
     * Creation date.
     * The create date of the bundle.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Discount type flag.
     * The discount type property is used to identify which
     * kind of the bundle discount are defined. This property
     * can contains two values:
     * <pre>
     *  'pro' => percentage definition @see \SwagBundle\Components\BundleComponentInterface::PERCENTAGE_DISCOUNT
     *  'abs' => absolute definition @see \SwagBundle\Components\BundleComponentInterface::ABSOLUTE_DISCOUNT
     * </pre>
     *
     * @param string $discountType
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;
    }

    /**
     * Discount type flag.
     * The discount type property is used to identify which
     * kind of the bundle discount are defined. This property
     * can contains two values:
     * <pre>
     *  'pro' => percentage definition @see \SwagBundle\Components\BundleComponentInterface::PERCENTAGE_DISCOUNT
     *  'abs' => absolute definition @see \SwagBundle\Components\BundleComponentInterface::ABSOLUTE_DISCOUNT
     * </pre>
     *
     * @return string
     */
    public function getDiscountType()
    {
        return $this->discountType;
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
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Display global flag.
     * This flag allows the shop administrator to define that the bundle is displayed
     * on every product, that is part of it.
     *
     * @param bool $displayGlobal
     *
     * @return $this
     */
    public function setDisplayGlobal($displayGlobal)
    {
        $this->displayGlobal = $displayGlobal;

        return $this;
    }

    /**
     * Display global flag.
     * This flag allows the shop administrator to define that the bundle is displayed
     * on every product, that is part of it.
     *
     * @return bool
     */
    public function getDisplayGlobal()
    {
        return $this->displayGlobal;
    }

    /**
     * sets the flag which decides if delivery time is shown and how
     *
     * @param int $displayDelivery
     */
    public function setDisplayDelivery($displayDelivery)
    {
        $this->displayDelivery = $displayDelivery;
    }

    /**
     * gets the flag which decides if delivery time is shown and how
     *
     * @return int
     */
    public function getDisplayDelivery()
    {
        return $this->displayDelivery;
    }

    /**
     * Stock validation flag.
     * The limited flag allows the shop administrator to define
     * that only a limited quantity of this bundle is available.
     *
     * @param bool $limited
     */
    public function setLimited($limited)
    {
        $this->limited = $limited;
    }

    /**
     * Stock validation flag.
     * The limited flag allows the shop administrator to define
     * that only a limited quantity of this bundle is available.
     *
     * @return bool
     */
    public function getLimited()
    {
        return $this->limited;
    }

    /**
     * Name of the bundle.
     * Used to identify the bundle over a custom name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Name of the bundle.
     * Used to identify the bundle over a custom name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function getShowName()
    {
        return $this->showName;
    }

    /**
     * @param bool $showName
     */
    public function setShowName($showName)
    {
        $this->showName = $showName;
    }

    /**
     * Order number of the bundle.
     * The order number is used to identify the bundle over a custom order
     * number without knowing all bundle ids.
     *
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * Order number of the bundle.
     * The order number is used to identify the bundle over a custom order
     * number without knowing all bundle ids.
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Position of the bundle in the detail page.
     *
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Position of the bundle in the detail page.
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Stock validation.
     * The quantity property contains the number of the bundle stock.
     * If the limited flag is set to true and the quantity falls down to zero,
     * the bundle is no more available.
     *
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * Stock validation.
     * The quantity property contains the number of the bundle stock.
     * If the limited flag is set to true and the quantity falls down to zero,
     * the bundle is no more available.
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Sells of the bundle.
     * Contains the count of the bundle sells.
     *
     * @param int $sells
     */
    public function setSells($sells)
    {
        $this->sells = $sells;
    }

    /**
     * Sells of the bundle.
     * Contains the count of the bundle sells.
     *
     * @return int
     */
    public function getSells()
    {
        return $this->sells;
    }

    /**
     * Type flag of the bundle.
     * The type property contains the definition of the bundle type.
     * This property can contains two different values:
     * <pre>
     *      1 => Normal bundle
     *      2 => Selectable bundle
     * </pre>
     *
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Type flag of the bundle.
     * The type property contains the definition of the bundle type.
     * This property can contains two different values:
     * <pre>
     *      1 => Normal bundle
     *      2 => Selectable bundle
     * </pre>
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * product positions of the bundle.
     * The products property contains an ArrayCollection with the defined bundle
     * products. This property don't contains as default the main product on which
     * the bundle are defined.
     * To get the main product as additional position, u can use the
     * \SwagBundle\Services\BundleMainProductService::getBundleMainProduct function.
     *
     * @return ArrayCollection|BundleProduct[]
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * product positions of the bundle.
     * The products property contains an ArrayCollection with the defined bundle
     * products. This property don't contains as default the main product on which
     * the bundle are defined.
     * To get the main product as additional position, u can use the
     * \SwagBundle\Services\BundleMainProductService::getBundleMainProduct function.
     *
     * @param ArrayCollection|BundleProduct[]
     *
     * @return \Shopware\Components\Model\ModelEntity
     */
    public function setArticles($articles)
    {
        return $this->setOneToMany($articles, BundleProduct::class, 'articles', 'bundle');
    }

    /**
     * Getter function for the bundle prices.
     * The prices property contains all defined bundle prices.
     * The bundle prices are defined for each customer group. If no price
     * defined for a single customer group, all customers of this group can't see the bundle
     * in the store front.
     *
     * @return ArrayCollection|Price[]
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * Getter function for the bundle prices.
     * The prices property contains all defined bundle prices.
     * The bundle prices are defined for each customer group. If no price
     * defined for a single customer group, all customers of this group can't see the bundle
     * in the store front.
     *
     * This function contains an auto loading which allows to pass the property data
     * as array which will be converted into \SwagBundle\Models\Price.
     *
     * @param ArrayCollection|Price[] $prices
     *
     * @return \Shopware\Components\Model\ModelEntity
     */
    public function setPrices($prices)
    {
        return $this->setOneToMany($prices, Price::class, 'prices', 'bundle');
    }

    /**
     * Main product of the bundle.
     * The product property contains the product on which the bundle created.
     * This product can be converted to an normal bundle product position by using the
     * \SwagBundle\Services\BundleMainProductService::getBundleMainProduct function.
     *
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Main product of the bundle.
     * The product property contains the product on which the bundle created.
     * This product can be converted to an normal bundle product position by using the
     * \SwagBundle\Services\BundleMainProductService::getBundleMainProduct function.
     *
     * @param Article $article
     */
    public function setArticle($article)
    {
        $this->article = $article;
    }

    /**
     * Allowed customer groups.
     * The customer groups property contains an ArrayCollection with all customer groups,
     * which are allowed to see/buy the bundle in the store front.
     *
     * @return ArrayCollection|Group[]
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * Allowed customer groups.
     * The customer groups property contains an ArrayCollection with all customer groups,
     * which are allowed to see/buy the bundle in the store front.
     *
     * @param ArrayCollection|Group[] $customerGroups
     */
    public function setCustomerGroups($customerGroups)
    {
        $this->customerGroups = $customerGroups;
    }

    /**
     * Limited variants of the bundle.
     * The bundle can be limited on specify variants of the product.
     * For example:<br>
     * - You want to bundle only the yellow t-shirt (SW-2000.1) because the customers
     * won't buy this. Now you can add the variant SW-2000.1 to this collection to display
     * the bundle only if the yellow t-shirt is selected.
     *
     * @param ArrayCollection|Detail[] $limitedDetails
     */
    public function setLimitedDetails($limitedDetails)
    {
        $this->limitedDetails = $limitedDetails;
    }

    /**
     * Limited variants of the bundle.
     * The bundle can be limited on specify variants of the article.
     * For example:<br>
     * - You want to bundle only the yellow t-shirt (SW-2000.1) because the customers
     * won't buy this. Now you can add the variant SW-2000.1 to this collection to display
     * the bundle only if the yellow t-shirt is selected.
     *
     * @return ArrayCollection|Detail[]
     */
    public function getLimitedDetails()
    {
        return $this->limitedDetails;
    }

    /**
     * Contains the current price.
     * This property is used by the \SwagBundle\Components\BundleComponent to
     * set the current price for the current frontend customer group.
     * This property isn't loaded from the database!
     *
     * @param Price $currentPrice
     */
    public function setCurrentPrice($currentPrice)
    {
        $this->currentPrice = $currentPrice;
    }

    /**
     * Contains the current price.
     * This property is used by the \SwagBundle\Components\BundleComponent to
     * set the current price for the current frontend customer group.
     * This property isn't loaded from the database!
     *
     * @return Price
     */
    public function getCurrentPrice()
    {
        return $this->currentPrice;
    }

    /**
     * Discount data of the bundle.
     * This property contains all data for the bundle discount.
     * The property is set by the \SwagBundle\Components\BundleComponent.
     * This property isn't loaded from the database!
     *
     * @param array $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * Discount data of the bundle.
     * This property contains all data for the bundle discount.
     * The property is set by the \SwagBundle\Components\BundleComponent.
     * This property isn't loaded from the database!
     *
     * @return array
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Total price of the bundle product positions.
     * The totalPrice property contains the summarized product prices
     * of the bundle.
     * This property isn't loaded from the database
     * used by \SwagBundle\Services\FullBundleService::getCalculatedBundle
     *
     * @param array $totalPrice
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * Total price of the bundle product positions.
     * The totalPrice property contains the summarized product prices
     * of the bundle.
     * This property isn't loaded from the database
     * used by \SwagBundle\Services\FullBundleService::getCalculatedBundle
     *
     * @return array
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Returns the price for the passed customer group.
     *
     * Model helper function which returns the \SwagBundle\Models\Price object for the passed customer group
     * key.
     *
     * @param string $customerGroupKey
     *
     * @return Price
     */
    public function getPriceForCustomerGroup($customerGroupKey)
    {
        $customerGroupPrice = null;
        foreach ($this->getUpdatedPrices() as $price) {
            if ($price->getCustomerGroup()->getKey() === $customerGroupKey) {
                $customerGroupPrice = $price;
            }
        }

        return $customerGroupPrice;
    }

    /**
     * Returns the allConfigured flag.
     * This flag is set to true, if all bundle product position, which has the
     * configurable flag, are configured through the customer.
     *
     * @return bool
     */
    public function getAllConfigured()
    {
        return $this->allConfigured;
    }

    /**
     * Returns the allConfigured flag.
     * This flag is set to true, if all bundle product position, which has the
     * configurable flag, are configured through the customer.
     *
     * @param bool $allConfigured
     */
    public function setAllConfigured($allConfigured)
    {
        $this->allConfigured = $allConfigured;
    }

    /**
     * Returns the product data as array.
     * This property contains the calculated product data of the bundle.
     * This property isn't loaded from the database, the \SwagBundle\Services\FullBundleService::getCalculatedBundle
     * function will set it.
     *
     * @return array
     */
    public function getProductData()
    {
        return $this->productData;
    }

    /**
     * Returns the product data as array.
     * This property contains the calculated product data of the bundle.
     * This property isn't loaded from the database, the \SwagBundle\Services\FullBundleService::getCalculatedBundle
     * function will set it.
     *
     * @param array $productData
     */
    public function setProductData(array $productData)
    {
        $this->productData = $productData;
    }

    /**
     * Contains updated prices for the shopware store front.
     *
     * @return ArrayCollection
     */
    public function getUpdatedPrices()
    {
        if (!$this->updatedPrices instanceof ArrayCollection) {
            if (is_array($this->updatedPrices)) {
                $this->setUpdatedPrices(new ArrayCollection($this->updatedPrices));
            } else {
                $this->setUpdatedPrices(new ArrayCollection());
            }
        }

        return $this->updatedPrices;
    }

    /**
     * Contains updated prices for the shopware store front.
     *
     * @param ArrayCollection $updatedPrices
     */
    public function setUpdatedPrices(ArrayCollection $updatedPrices)
    {
        $this->updatedPrices = $updatedPrices;
    }

    /**
     * @return int
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * @param array
     */
    public function setLongestShippingTimeProduct($longestShippingTimeProduct)
    {
        $this->longestShippingTimeProduct = $longestShippingTimeProduct;
    }

    /**
     * @return array
     */
    public function getLongestShippingTimeProduct()
    {
        return $this->longestShippingTimeProduct;
    }
}
