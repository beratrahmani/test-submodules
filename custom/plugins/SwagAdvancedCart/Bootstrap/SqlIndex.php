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

namespace SwagAdvancedCart\Bootstrap;

use Doctrine\DBAL\Connection;

class SqlIndex
{
    const BASKET_SAVED = 's_order_basket_saved';
    const BASKET_SAVED_ITEMS = 's_order_basket_saved_items';

    /**
     * @var array
     */
    private $indexes;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function createIndexes102()
    {
        $sql = 'ALTER TABLE `s_order_basket_saved_items` ADD INDEX ( `basket_id` );';
        if (!$this->checkIfIndexExists(self::BASKET_SAVED_ITEMS . '_basket_id')) {
            $this->connection->exec($sql);
        }
    }

    public function createIndexes110()
    {
        $sql = [
            self::BASKET_SAVED . '_' . 'cookie_value' => 'ALTER TABLE s_order_basket_saved ADD INDEX (cookie_value);',
            self::BASKET_SAVED . '_' . 'shop_id' => 'ALTER TABLE s_order_basket_saved ADD INDEX (shop_id);',
            self::BASKET_SAVED . '_' . 'user_id' => 'ALTER TABLE s_order_basket_saved ADD INDEX (user_id);',
            self::BASKET_SAVED_ITEMS . '_' . 'article_ordernumber' => 'ALTER TABLE s_order_basket_saved_items ADD INDEX (article_ordernumber);',
        ];

        foreach ($sql as $key => $query) {
            if (!$this->checkIfIndexExists($key)) {
                $this->connection->exec($query);
            }
        }
    }

    /**
     * @return array
     */
    private function getIndexes()
    {
        if (empty($this->indexes)) {
            $this->indexes = $this->generateIndexes();
        }

        return $this->indexes;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function checkIfIndexExists($name)
    {
        return in_array($name, $this->getIndexes(), false);
    }

    /**
     * @return array
     */
    private function generateIndexes()
    {
        $sql = [
            self::BASKET_SAVED => 'SHOW INDEX FROM s_order_basket_saved;',
            self::BASKET_SAVED_ITEMS => 'SHOW INDEX FROM s_order_basket_saved_items;',
        ];

        $indexes = [];
        $result = [];
        $result[self::BASKET_SAVED] = $this->connection->executeQuery($sql[self::BASKET_SAVED]);
        $result[self::BASKET_SAVED_ITEMS] = $this->connection->executeQuery($sql[self::BASKET_SAVED_ITEMS]);

        foreach ($result as $key => $singleResult) {
            foreach ($singleResult as $index) {
                $indexes[] = $key . '_' . $index['Column_name'];
            }
        }

        return $indexes;
    }
}
