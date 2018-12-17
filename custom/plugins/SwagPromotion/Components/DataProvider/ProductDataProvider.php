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

namespace SwagPromotion\Components\DataProvider;

use Enlight_Components_Db_Adapter_Pdo_Mysql as PdoConnection;
use SwagPromotion\Components\MetaData\FieldInfo;

/**
 * Holds context data for all products in basket.
 */
class ProductDataProvider implements DataProvider
{
    /**
     * @var PdoConnection
     */
    private $db;

    /**
     * ProductDataProvider constructor.
     *
     * @param PdoConnection $db
     */
    public function __construct(PdoConnection $db)
    {
        $this->db = $db;
    }

    /**
     * Get product information
     *
     * {@inheritdoc}
     */
    public function get($context = null)
    {
        if (!$context) {
            return [];
        }

        return $this->getProductContext($context);
    }

    /**
     * Read out product data
     *
     * @param array $orderNumbers
     *
     * @return array
     */
    private function getProductContext(array $orderNumbers)
    {
        $products = $this->getBaseProducts($orderNumbers);
        $lookup = [];
        foreach ($products as $idx => $product) {
            $lookup[$product['articleID']][] = $idx;
        }

        $categories = $this->getProductCategories(array_column($products, 'articleID'));

        foreach ($this->getBasketAttributes() as $attributes) {
            foreach ($attributes as $key => $attribute) {
                if (!isset($lookup[$attributes['articleID']])) {
                    continue;
                }
                foreach ($lookup[$attributes['articleID']] as $idx) {
                    $products[$idx]['basketAttribute::' . $key] = $attribute;
                }
            }
        }

        // set categories into base product
        foreach ($categories as $row) {
            foreach ($lookup[$row['articleID']] as $idx) {
                $products[$idx]['categories'][] = $row;
            }
        }

        return $products;
    }

    /**
     * Enrich the product data with basket attributes, if possible
     *
     * @return array
     */
    private function getBasketAttributes()
    {
        $sql = 'SELECT basket.articleID, attributes.*
                FROM s_order_basket basket
                LEFT JOIN s_order_basket_attributes attributes ON attributes.basketID = basket.id
                WHERE basket.sessionID = :session
                  AND basket.modus = 0';

        return $this->db->fetchAll($sql, ['session' => Shopware()->Session()->get('sessionId')]);
    }

    /**
     * Return the base product information
     *
     * @param array $orderNumbers
     *
     * @return array
     */
    private function getBaseProducts(array $orderNumbers)
    {
        $questionMarks = implode(', ', array_fill(0, count($orderNumbers), '?'));

        $info = new FieldInfo();
        $info = array_keys($info->get()['product']);

        $mapping = [
            'product' => 'articles',
            'detail' => 'details',
            'productAttribute' => 'attributes',
            'price' => 'prices',
            'supplier' => 'supplier',
        ];

        $fields = implode(
            ', ',
            array_map(
                function ($field) use ($mapping) {
                    list($type, $rest) = explode('::', $field, 2);

                    $table = $mapping[$type];

                    return "{$table}.{$rest} as \"{$field}\"";
                },
                // sort out categories
                array_filter(
                    $info,
                    function ($field) {
                        return strpos($field, 'categories') !== 0;
                    }
                )
            )
        );

        $sql = "SELECT details.ordernumber, {$fields}, basket.quantity, basket.price, basket.netprice, details.articleID

        FROM s_articles_details details

        LEFT JOIN s_order_basket basket
        on basket.ordernumber = details.ordernumber
        AND basket.sessionID = ?
        AND modus = 0

        LEFT JOIN s_articles_attributes attributes
        ON attributes.articledetailsID = details.id

        LEFT JOIN s_articles articles
        ON articles.id = details.articleID

        LEFT JOIN s_articles_prices prices
        ON prices.articledetailsID = details.id

        LEFT JOIN s_articles_supplier supplier
        ON supplier.id = articles.supplierID

        WHERE details.ordernumber IN ({$questionMarks})";

        $data = $this->db->fetchAssoc(
            $sql,
            array_merge([Shopware()->Session()->get('sessionId')], array_keys($orderNumbers))
        );

        foreach ($data as $orderNumber => $product) {
            $data[$orderNumber]['quantity'] = $orderNumbers[$orderNumber];
        }

        return $data;
    }

    /**
     * Return categories for the given articleIds
     *
     * @param array $articleIds
     *
     * @return array
     */
    private function getProductCategories(array $articleIds)
    {
        return $this->db->fetchAll(
            'SELECT ro.articleID, attributes.*, categories.*
            FROM s_articles_categories_ro ro

            INNER JOIN s_categories categories
            ON categories.id = ro.categoryID

            LEFT JOIN s_categories_attributes attributes
            ON attributes.categoryID = ro.categoryID

            WHERE articleID IN (' . implode(', ', $articleIds) . ')'
        );
    }
}
