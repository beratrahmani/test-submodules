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

use Doctrine\DBAL\Connection;

class AboAddressService implements AboAddressServiceInterface
{
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

    /**
     * {@inheritdoc}
     */
    public function getOrdersShippingAddresses(array $subscriptionIds)
    {
        return $this->connection->createQueryBuilder()
            ->select('subscription.id, address.*, country.countryname as countryName')
            ->from('s_user_addresses', 'address')
            ->leftJoin('address', 's_plugin_swag_abo_commerce_orders', 'subscription', 'address.id = subscription.shipping_address_id')
            ->leftJoin('address', 's_core_countries', 'country', 'address.country_id = country.id')
            ->where('subscription.id IN (:ids)')
            ->setParameter('ids', $subscriptionIds, CONNECTION::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrdersBillingAddresses(array $subscriptionIds)
    {
        return $this->connection->createQueryBuilder()
            ->select('subscription.id, address.*, country.countryname as countryName')
            ->from('s_user_addresses', 'address')
            ->leftJoin('address', 's_plugin_swag_abo_commerce_orders', 'subscription', 'address.id = subscription.billing_address_id')
            ->leftJoin('address', 's_core_countries', 'country', 'address.country_id = country.id')
            ->where('subscription.id IN (:ids)')
            ->setParameter('ids', $subscriptionIds, CONNECTION::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function updateSubscriptionBillingAddress($subscriptionId, $addressId)
    {
        $sql = <<<SQL
UPDATE s_plugin_swag_abo_commerce_orders
SET billing_address_id = :addressId
WHERE id = :subscriptionId    
SQL;

        $this->connection->executeQuery($sql, ['addressId' => $addressId, 'subscriptionId' => $subscriptionId]);
    }

    /**
     * {@inheritdoc}
     */
    public function updateSubscriptionShippingAddress($subscriptionId, $addressId)
    {
        $sql = <<<SQL
UPDATE s_plugin_swag_abo_commerce_orders
SET shipping_address_id = :addressId
WHERE id = :subscriptionId    
SQL;

        $this->connection->executeQuery($sql, ['addressId' => $addressId, 'subscriptionId' => $subscriptionId]);
    }
}