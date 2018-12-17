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

namespace SwagAdvancedCart\Services\Dependencies;

use Doctrine\DBAL\Connection;

class PluginDependencies implements PluginDependenciesInterface
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @param Connection $dbalConnection
     */
    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function isPluginInstalled($technicalPluginName)
    {
        $result = $this->dbalConnection->fetchColumn(
            'SELECT id FROM s_core_plugins WHERE name LIKE :pluginName;',
            ['pluginName' => $technicalPluginName]);

        return !empty($result);
    }

    /**
     * {@inheritdoc}
     */
    public function isBundleArticleInBasket(array $basketIds)
    {
        $columnExists = $this->dbalConnection->fetchColumn("SHOW COLUMNS FROM `s_order_basket_attributes` LIKE 'bundle_id'");

        if (!$columnExists) {
            return false;
        }

        $result = $this->dbalConnection->fetchColumn(
            'SELECT id FROM s_order_basket_attributes WHERE basketID IN (:basketIds) AND bundle_id IS NOT NULL;',
            ['basketIds' => implode(',', $basketIds)]
        );

        return !empty($result);
    }
}
