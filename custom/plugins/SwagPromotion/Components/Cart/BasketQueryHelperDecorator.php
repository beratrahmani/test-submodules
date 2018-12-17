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

namespace SwagPromotion\Components\Cart;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\Cart\BasketQueryHelper;
use Shopware\Components\Cart\BasketQueryHelperInterface;
use Shopware\Components\Cart\Struct\DiscountContext;

class BasketQueryHelperDecorator implements BasketQueryHelperInterface
{
    const ATTRIBUTE_COLUMN_PROMOTION_ID = 'swag_promotion_id';

    /**
     * @var BasketQueryHelperInterface
     */
    private $basketQueryHelper;

    /**
     * @param BasketQueryHelperInterface $basketQueryHelper
     */
    public function __construct(BasketQueryHelperInterface $basketQueryHelper)
    {
        $this->basketQueryHelper = $basketQueryHelper;
    }

    /**
     * @param DiscountContext $discountContext
     *
     * @return QueryBuilder
     */
    public function getPositionPricesQuery(DiscountContext $discountContext)
    {
        $query = $this->basketQueryHelper->getPositionPricesQuery($discountContext);

        $joinAlias = $this->getJoinAlias(
            $query->getQueryPart('join'),
            BasketQueryHelper::BASKET_ATTRIBUTE_TABLE_NAME
        );

        if (!$joinAlias) {
            $joinAlias = 'attributes';
            $query->join(
                BasketQueryHelper::BASKET_TABLE_ALIAS,
                BasketQueryHelper::BASKET_ATTRIBUTE_TABLE_NAME,
                $joinAlias,
                BasketQueryHelper::BASKET_TABLE_ALIAS . '.id' . ' = ' . $joinAlias . '.basketID'
            );
        }

        $query->andWhere(
            $joinAlias . '.swag_is_free_good_by_promotion_id IS NULL'
        );

        return $query;
    }

    /**
     * @param DiscountContext $discountContext
     *
     * @return QueryBuilder
     */
    public function getInsertDiscountQuery(DiscountContext $discountContext)
    {
        return $this->basketQueryHelper->getInsertDiscountQuery($discountContext);
    }

    /**
     * @param DiscountContext $discountContext
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getInsertDiscountAttributeQuery(DiscountContext $discountContext)
    {
        $query = $this->basketQueryHelper->getInsertDiscountAttributeQuery($discountContext);

        $attribute = $discountContext->getAttribute(self::ATTRIBUTE_COLUMN_PROMOTION_ID);

        if (!$attribute) {
            return $query;
        }

        $query->setValue(
            self::ATTRIBUTE_COLUMN_PROMOTION_ID,
            ':' . self::ATTRIBUTE_COLUMN_PROMOTION_ID
        );

        $query->setParameter(
            self::ATTRIBUTE_COLUMN_PROMOTION_ID,
            $attribute->get('id')
        );

        return $query;
    }

    /**
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->basketQueryHelper->getLastInsertId();
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
