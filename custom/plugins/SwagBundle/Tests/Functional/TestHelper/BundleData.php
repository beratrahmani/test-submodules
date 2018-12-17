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

namespace SwagBundle\Tests\Functional\TestHelper;

use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;

class BundleData
{
    /**
     * @return array
     */
    public static function getBundleTableNames()
    {
        return [
            's_articles_bundles',
            's_articles_bundles_articles',
            's_articles_bundles_customergroups',
            's_articles_bundles_prices',
            's_articles_bundles_stint',
        ];
    }

    /**
     * @return array
     */
    public static function getBundleData()
    {
        return [
            [
                'id' => 1,
                'articleID' => 178,
                '`name`' => '"Test Bundle 01"',
                'show_name' => 1,
                'active' => 1,
                'description' => '"Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor"',
                'rab_type' => '"pro"',
                'ordernumber' => '"sw-b-08154711"',
                'max_quantity_enable' => 0,
                'display_global' => 1,
                'display_delivery' => 0,
                'max_quantity' => 0,
                'datum' => '"2015-10-29 11:07:01"',
                'sells' => 0,
                'bundle_type' => 2,
                'bundle_position' => 0,
                'bundleArticles' => [
                    [
                        'id' => 1,
                        'bundle_id' => 1,
                        'article_detail_id' => 394,
                        'quantity' => 1,
                        'configurable' => 1,
                        'position' => 1,
                    ], [
                        'id' => 2,
                        'bundle_id' => 1,
                        'article_detail_id' => 322,
                        'quantity' => 1,
                        'configurable' => 1,
                        'position' => 1,
                    ], [
                        'id' => 3,
                        'bundle_id' => 1,
                        'article_detail_id' => 27,
                        'quantity' => 1,
                        'configurable' => 0,
                        'position' => 2,
                    ], [
                        'id' => 4,
                        'bundle_id' => 1,
                        'article_detail_id' => 28,
                        'quantity' => 1,
                        'configurable' => 0,
                        'position' => 2,
                    ],
                ],
                'bundleCustomerGroups' => [[
                    'id' => 1,
                    'bundle_id' => 1,
                    'customer_group_id' => 1,
                ]],
                'bundlePrices' => [[
                    'id' => 1,
                    'bundle_id' => 1,
                    'customer_group_id' => 1,
                    'price' => 10,
                ]],
            ],
            [
                'id' => 2,
                'articleID' => 272,
                '`name`' => '"Test Bundle 02"',
                'show_name' => 1,
                'active' => 1,
                'description' => '"Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor"',
                'rab_type' => '"pro"',
                'ordernumber' => '"sw-b-08154712"',
                'max_quantity_enable' => 0,
                'display_global' => 1,
                'display_delivery' => 0,
                'max_quantity' => 0,
                'datum' => '"2016-10-29 11:07:01"',
                'sells' => 0,
                'bundle_type' => 1,
                'bundle_position' => 0,
                'bundleArticles' => [
                    [
                        'id' => 5,
                        'bundle_id' => 2,
                        'article_detail_id' => 15,
                        'quantity' => 1,
                        'configurable' => 1,
                        'position' => 1,
                    ], [
                        'id' => 6,
                        'bundle_id' => 2,
                        'article_detail_id' => 123,
                        'quantity' => 1,
                        'configurable' => 1,
                        'position' => 1,
                    ], [
                        'id' => 7,
                        'bundle_id' => 2,
                        'article_detail_id' => 27,
                        'quantity' => 1,
                        'configurable' => 0,
                        'position' => 1,
                    ],
                ],
                'bundleCustomerGroups' => [
                    [
                        'id' => 2,
                        'bundle_id' => 2,
                        'customer_group_id' => 1,
                    ],
                ],
                'bundlePrices' => [
                    [
                        'id' => 2,
                        'bundle_id' => 2,
                        'customer_group_id' => 1,
                        'price' => 15,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getListProducts()
    {
        return [
            new ListProduct(178, 407, 'SW10178'),
            new ListProduct(272, 827, 'SW10239'),
        ];
    }
}
