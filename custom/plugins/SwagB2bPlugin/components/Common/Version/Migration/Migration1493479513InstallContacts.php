<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Acl\Framework\AclDdlService;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Shopware\B2B\Contact\Framework\DependencyInjection\ContactFrameworkConfiguration;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479513InstallContacts implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479513;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            CREATE TABLE `b2b_debtor_contact` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `auth_id` INT(11) NULL DEFAULT NULL,
                `password` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
                `encoder` VARCHAR(255) NOT NULL DEFAULT \'md5\' COLLATE \'utf8_unicode_ci\',
                `email` VARCHAR(70) NOT NULL COLLATE \'utf8_unicode_ci\',
                `active` INT(1) NOT NULL DEFAULT \'0\',
                `language` VARCHAR(10) NOT NULL COLLATE \'utf8_unicode_ci\',
                `title` VARCHAR(100) NULL DEFAULT NULL COLLATE \'utf8_unicode_ci\',
                `salutation` VARCHAR(30) NULL DEFAULT NULL COLLATE \'utf8_unicode_ci\',
                `firstname` VARCHAR(255) NULL DEFAULT NULL COLLATE \'utf8_unicode_ci\',
                `lastname` VARCHAR(255) NULL DEFAULT NULL COLLATE \'utf8_unicode_ci\',
                `department` VARCHAR(255) NULL DEFAULT NULL COLLATE \'utf8_unicode_ci\',
                `context_owner_id` INT(11) NOT NULL,

                PRIMARY KEY (`id`),                
                UNIQUE INDEX `email` (`email`),
                INDEX `FK_b2b_debtor_contact_context_owner_id` (`context_owner_id`),

                CONSTRAINT `b2b_debtor_contact_auth_owner_id_FK` FOREIGN KEY (`context_owner_id`) 
                  REFERENCES `b2b_store_front_auth` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
                  
                CONSTRAINT `b2b_debtor_contact_auth_id_FK` FOREIGN KEY (`auth_id`) 
                  REFERENCES `b2b_store_front_auth` (`id`) ON UPDATE NO ACTION ON DELETE SET NULL
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
            AUTO_INCREMENT=100;
        ');
    }

    public function updateThroughServices(Container $container)
    {
        foreach (ContactFrameworkConfiguration::createAclTables() as $table) {
            AclDdlService::create()->createTable($table);
        }
    }
}
