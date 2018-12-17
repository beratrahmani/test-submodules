<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479533InstallPrices implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479533;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->query('
            CREATE TABLE `b2b_prices` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `debtor_id` int(11) NOT NULL,
              `price` double NOT NULL,
              `from` int(11) NOT NULL,
              `to` int(11) DEFAULT NULL,
              `articles_details_id` INT(11) unsigned NOT NULL,
            
              PRIMARY KEY (`id`),
            
              UNIQUE INDEX `b2b_debtor_from_to_article_idx` (`debtor_id`, `from`, `to`, `articles_details_id`),
              INDEX `b2b_prices_articles_details_idx` (`articles_details_id`),
              INDEX `b2b_prices_debtor_idx` (`debtor_id`),
            
              CONSTRAINT `FK_prices_debtor_id` FOREIGN KEY (`debtor_id`)
                REFERENCES `s_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `FK_prices_articles_details_id` FOREIGN KEY (`articles_details_id`)
                REFERENCES `s_articles_details` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
            ;
        ');
    }

    public function updateThroughServices(Container $container)
    {
    }
}
