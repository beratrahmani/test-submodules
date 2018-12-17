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
use Shopware\Models\Article\Detail;

/**
 * Bundle product model.
 * This model is used to assign a product/variant to the bundle as new bundle product position.
 * All assigned products are defines the whole bundle.
 * The different product position can be de-/selected if the bundle is defined as selectable bundle.
 * The bundle discount and the bundle price are depend to the defined bundle product positions.
 * The main product on which the bundle are defined isn't defined as SwagBundle\Models\Article.
 * To get the main product as normal bundle position use the
 * \SwagBundle\Services\BundleMainProductService::getBundleMainProduct function.
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity
 * @ORM\Table(name="s_articles_bundles_articles")
 */
class Article extends ModelEntity
{
    /**
     * The $bundle property contains the instance of \SwagBundle\Models\Bundle of the parent bundle.
     *
     * @ORM\ManyToOne(targetEntity="SwagBundle\Models\Bundle", inversedBy="articles")
     * @ORM\JoinColumn(name="bundle_id", referencedColumnName="id")
     *
     * @var Bundle
     */
    protected $bundle;

    /**
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Detail")
     * @ORM\JoinColumn(name="article_detail_id", referencedColumnName="id")
     *
     * @var Detail
     */
    protected $articleDetail;

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
     * The product detail id of the selected product variant.
     * Can be defined over the backend module.
     * Used as foreign key for the product detail association.
     * Has no getter and setter. Only defined to have access on the order number in queries without joining the
     * s_articles_details.
     *
     * @ORM\Column(name="article_detail_id", type="integer", nullable=false)
     *
     * @var string
     */
    private $articleDetailId;

    /**
     * Id of the bundle.
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
     * Flag for configurator products.
     * If the configurable flag is set to true, the customer can configure the product variant in the store front like a
     * normal configurator product over the groups and options.
     * If the configurable flag is set to false, the customer has no opportunity to configure the product variant in the
     * frontend.
     *
     * @ORM\Column(name="configurable", type="boolean", nullable=false)
     *
     * @var bool
     */
    private $configurable = false;

    /**
     * Contains the quantity of the bundled product.
     * The bundle product quantity can be configured over the backend module.
     *
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     *
     * @var int
     */
    private $quantity = 1;

    /**
     * Contains the position of the bundled product.
     * The bundle product position can be configured over the backend module.
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     *
     * @var int
     */
    private $position = 0;

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
    public function getConfigurable()
    {
        return $this->configurable;
    }

    /**
     * @param bool $configurable
     */
    public function setConfigurable($configurable)
    {
        $this->configurable = $configurable;
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
     * @return Detail
     */
    public function getArticleDetail()
    {
        return $this->articleDetail;
    }

    /**
     * @param Detail $articleDetail
     */
    public function setArticleDetail($articleDetail)
    {
        $this->articleDetail = $articleDetail;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getBundleId()
    {
        return $this->bundleId;
    }
}
