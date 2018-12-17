<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1521801956RemoveUnusedOrderAttributes implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1521801956;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
        $attributeService = $container->get('shopware_attribute.crud_service');

        $attributeService->delete('s_order_attributes', 'b2b_requested_delivery_date');
        $attributeService->delete('s_order_attributes', 'b2b_order_reference');
        $attributeService->delete('s_order_attributes', 'b2b_clearance_comment');
    }
}
