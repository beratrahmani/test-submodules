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

class AboPaymentService implements AboPaymentServiceInterface
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
    public function getAboCommercePaymentMeansIds()
    {
        return $this->connection->createQueryBuilder()
            ->select('payment_id')
            ->from('s_plugin_swag_abo_commerce_settings_paymentmeans')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * {@inheritdoc}
     */
    public function updateSubscriptionPaymentMethod($subscriptionId, $paymentId)
    {
        $sql = <<<SQL
UPDATE s_plugin_swag_abo_commerce_orders
SET payment_id = :paymentId
WHERE id = :subscriptionId    
SQL;

        $this->connection->executeQuery($sql, ['paymentId' => $paymentId, 'subscriptionId' => $subscriptionId]);
    }
}