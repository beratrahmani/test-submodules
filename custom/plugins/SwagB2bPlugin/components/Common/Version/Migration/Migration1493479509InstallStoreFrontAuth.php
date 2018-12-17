<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479509InstallStoreFrontAuth implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479509;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            CREATE TABLE `b2b_store_front_auth` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `context_owner_id` INT(11) NOT NULL,
                `provider_key` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
                `provider_context` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
                
                PRIMARY KEY (`id`),
                INDEX `b2b_store_front_auth_context_owner_id` (context_owner_id),                
                UNIQUE INDEX `b2b_store_front_auth_provider_key_provider_value_idx` (`provider_key`, `provider_context`)
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
            AUTO_INCREMENT=100;
        ');
    }

    public function updateThroughServices(Container $container)
    {
    }
}
