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

namespace SwagBundle\Services\Discount;

use Shopware\Components\Cart\BasketQueryHelper;
use Shopware\Components\Cart\BasketQueryHelperInterface;
use Shopware\Components\Cart\Struct\DiscountContext;

class BundleBasketQueryHelper extends BasketQueryHelper
{
    const JOIN_ALIAS = 'basketAttributes';

    /**
     * {@inheritdoc}
     */
    public function getPositionPricesQuery(DiscountContext $discountContext)
    {
        $query = parent::getPositionPricesQuery($discountContext);

        $bundleData = $discountContext->getAttribute('bundleDiscount');
        if (!$bundleData) {
            return $query;
        }

        $basketAlias = BasketQueryHelperInterface::BASKET_TABLE_ALIAS;
        $basketAttributesTableName = BasketQueryHelperInterface::BASKET_ATTRIBUTE_TABLE_NAME;

        $query->where($basketAlias . '.sessionID = :session');

        $joinAlias = $this->getJoinAlias($query->getQueryPart('join'), $basketAttributesTableName);

        if ($joinAlias === '') {
            $joinAlias = self::JOIN_ALIAS;

            $query->leftJoin(
                $basketAlias,
                $basketAttributesTableName,
                $joinAlias,
                $basketAlias . '.id = ' . $joinAlias . '.basketID'
            );
        }

        $query->andWhere($joinAlias . '.bundle_package_id = :bundleMainProductBasketId');
        $query->setParameter('bundleMainProductBasketId', $bundleData->get('bundleMainProductBasketId'));

        if ($bundleData->exists('bundleDiscountOrderNumber')) {
            $bundleDiscountOrderNumber = $bundleData->get('bundleDiscountOrderNumber');
            $query->andWhere('ordernumber != :bundleDiscountOrderNumber');
            $query->setParameter('bundleDiscountOrderNumber', $bundleDiscountOrderNumber);
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getInsertDiscountAttributeQuery(DiscountContext $discountContext)
    {
        $query = parent::getInsertDiscountAttributeQuery($discountContext);

        $bundleData = $discountContext->getAttribute('bundleDiscount');
        if (!$bundleData) {
            return $query;
        }

        $query->setValue('bundle_id', ':bundleId');
        $query->setValue('bundle_article_ordernumber', ':bundleMainProductNumber');
        $query->setValue('bundle_package_id', ':bundleMainProductBasketId');
        $query->setParameter('bundleId', $bundleData->get('bundleId'));
        $query->setParameter('bundleMainProductNumber', $bundleData->get('bundleMainProductNumber'));
        $query->setParameter('bundleMainProductBasketId', $bundleData->get('bundleMainProductBasketId'));

        return $query;
    }

    /**
     * @param array[] $joins
     * @param string  $table
     *
     * @return string
     */
    private function getJoinAlias(array $joins, $table)
    {
        if (!array_key_exists(BasketQueryHelperInterface::BASKET_TABLE_ALIAS, $joins)) {
            return '';
        }

        foreach ($joins[BasketQueryHelperInterface::BASKET_TABLE_ALIAS] as $join) {
            if ($join['joinTable'] === $table) {
                return $join['joinAlias'];
            }
        }

        return '';
    }
}
