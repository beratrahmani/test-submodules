<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1498027169InstallInStock implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1498027169;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            CREATE TABLE `b2b_in_stocks` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `auth_id` int(11) NOT NULL,
              `in_stock` double NOT NULL,
              `articles_details_id` INT(11) unsigned NOT NULL,
            
              PRIMARY KEY (`id`),
            
              UNIQUE INDEX `b2b_debtor_from_to_article_idx` (`auth_id`, `articles_details_id`),
              INDEX `b2b_in_stocks_articles_details_idx` (`articles_details_id`),
              INDEX `b2b_in_stocks_auth_idx` (`auth_id`),
            
              CONSTRAINT `FK_in_stocks_auth_id` FOREIGN KEY (`auth_id`)
                REFERENCES `b2b_store_front_auth` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `FK_in_stocks_articles_details_id` FOREIGN KEY (`articles_details_id`)
                REFERENCES `s_articles_details` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
            ;
        ');
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
