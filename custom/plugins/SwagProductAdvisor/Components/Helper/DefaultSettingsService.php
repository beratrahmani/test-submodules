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

namespace SwagProductAdvisor\Components\Helper;

use Doctrine\DBAL\Connection;
use SwagProductAdvisor\Structs\DefaultSettings;

class DefaultSettingsService implements DefaultSettingsServiceInterface
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * DefaultSettingsService constructor.
     *
     * @param Connection $dbalConnection
     */
    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSettings()
    {
        $settings = $this->dbalConnection->createQueryBuilder()
            ->select(['shop.id as shopId', 'currency.id as currencyId'])
            ->from('s_core_shops', 'shop')
            ->where('shop.default = 1')
            ->join('shop', 's_core_shop_currencies', 'shocur', 'shocur.shop_id = shop.id')
            ->join('shocur', 's_core_currencies', 'currency', 'currency.id = shocur.currency_id')
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        $settings['customerGroup'] = $this->dbalConnection->createQueryBuilder()
            ->select(['cg.groupkey'])
            ->from('s_core_customergroups', 'cg')
            ->where('tax != 0')
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);

        return new DefaultSettings($settings['shopId'], $settings['currencyId'], $settings['customerGroup']);
    }
}
