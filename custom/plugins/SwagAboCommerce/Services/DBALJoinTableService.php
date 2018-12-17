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

namespace SwagAboCommerce\Services;

use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;

class DBALJoinTableService implements DBALJoinTableServiceInterface
{
    const STATE_INCLUDES_ABO = 'swag_abo_commerce';

    /**
     * {@inheritdoc}
     */
    public function joinTable(QueryBuilder $query)
    {
        if ($query->hasState(self::STATE_INCLUDES_ABO)) {
            return;
        }

        $query->leftJoin(
            'product',
            's_plugin_swag_abo_commerce_articles',
            'aboProduct',
            'aboProduct.article_id = product.id AND aboProduct.active = true'
        );

        $query->addState(self::STATE_INCLUDES_ABO);
    }
}
