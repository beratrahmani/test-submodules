<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Shopware\B2B\Debtor\Framework\DebtorRepository;
use Symfony\Component\DependencyInjection\Container;

class Migration1499168548SalesRepresentativeToContact implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1499168548;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            CREATE TABLE `b2b_sales_representative_clients` (
              `sales_representative_id` INT(11) NOT NULL,
              `client_id` int(11) NOT NULL,
              
              PRIMARY KEY (`sales_representative_id`, `client_id`),
              
              CONSTRAINT `FK_sales_representative` FOREIGN KEY (`sales_representative_id`)
                REFERENCES `s_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `FK_client_id` FOREIGN KEY (`client_id`)
                REFERENCES `b2b_store_front_auth` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
            ;
        ');

        $connection->exec('
            INSERT INTO `b2b_sales_representative_clients`
              SELECT 
                attr.b2b_sales_representative_id as sales_representative_id,
                auth.id as client_id
              FROM `s_user_attributes` as attr
              INNER JOIN `b2b_store_front_auth` as auth
              ON attr.userID = auth.provider_context
              AND auth.provider_key = \'' . addslashes(DebtorRepository::class) . '\'
              AND attr.b2b_sales_representative_id > 0
            ;
        ');
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
        $attributeService = $container->get('shopware_attribute.crud_service');

        if ($attributeService->get('s_user_attributes', 'b2b_sales_representative_id')) {
            $attributeService->delete('s_user_attributes', 'b2b_sales_representative_id');
        }
    }
}
