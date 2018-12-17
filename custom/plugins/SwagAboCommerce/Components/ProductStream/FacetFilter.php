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

namespace SwagAboCommerce\Components\ProductStream;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Components\ProductStream\FacetFilterInterface;

class FacetFilter implements FacetFilterInterface
{
    /**
     * @var FacetFilterInterface
     */
    private $coreFacetFilter;

    /**
     * @param FacetFilterInterface $coreFacetFilter
     */
    public function __construct(FacetFilterInterface $coreFacetFilter)
    {
        $this->coreFacetFilter = $coreFacetFilter;
    }

    /**
     * Add the abo-filter to the collection
     * {@inheritdoc}
     */
    public function add(Criteria $criteria)
    {
        $this->coreFacetFilter->add($criteria);

        if ($criteria->hasBaseCondition('abo_commerce_product')) {
            $criteria->removeFacet('abo_commerce_product');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $facets, Criteria $criteria)
    {
        return $this->coreFacetFilter->filter($facets, $criteria);
    }
}
