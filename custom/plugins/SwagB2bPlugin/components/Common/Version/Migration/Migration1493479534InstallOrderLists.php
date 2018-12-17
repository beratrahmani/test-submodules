<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Acl\Framework\AclDdlService;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Shopware\B2B\OrderList\Framework\DependencyInjection\OrderListFrameworkConfiguration;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479534InstallOrderLists implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479534;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->query('
            CREATE TABLE `b2b_order_list` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(70) NOT NULL COLLATE \'utf8_unicode_ci\',
            `list_id` INT(11) NOT NULL,
            `context_owner_id` INT(11) NOT NULL,
                
            PRIMARY KEY (`id`),
                
            INDEX `FK_b2b_order_list_context_owner_id` (`context_owner_id`),
            INDEX `FK_b2b_order_list_list_id` (`list_id`),
            
            CONSTRAINT `FK_b2b_order_list_auth_owner_id_FK` FOREIGN KEY (`context_owner_id`) 
              REFERENCES `b2b_store_front_auth` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
            
            CONSTRAINT `FK_b2b_order_list_line_item_list` FOREIGN KEY (`list_id`) 
              REFERENCES `b2b_line_item_list` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
            )
        ');
    }

    public function updateThroughServices(Container $container)
    {
        $attributeService = $container->get('shopware_attribute.crud_service');
        $attributeService->update('s_order_basket_attributes', 'b2b_order_list', 'string');

        foreach (OrderListFrameworkConfiguration::createAclTables() as $table) {
            AclDdlService::create()->createTable($table);
        }
    }
}
