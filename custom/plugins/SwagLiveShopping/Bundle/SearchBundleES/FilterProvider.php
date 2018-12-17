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

namespace SwagLiveShopping\Bundle\SearchBundleES;

use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\ExistsQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class FilterProvider
{
    /**
     * @param ShopContextInterface $context
     *
     * @return BoolQuery
     */
    public static function getBoolFilter(ShopContextInterface $context)
    {
        $now = new \DateTime();
        $filter = new BoolQuery();
        $filter->add(new ExistsQuery('attributes.live_shopping'));
        $filter->add(new MatchQuery('attributes.live_shopping.customer_group_keys', $context->getCurrentCustomerGroup()->getKey()));
        $filter->add(new RangeQuery('attributes.live_shopping.valid_from', ['lte' => $now->format('Y-m-d H:i:s')]));
        $filter->add(new RangeQuery('attributes.live_shopping.valid_to', ['gte' => $now->format('Y-m-d H:i:s')]));

        return $filter;
    }
}
