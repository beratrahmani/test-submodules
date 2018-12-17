<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1517833946OrderNumber implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1517833946;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec(
            '
            CREATE TABLE b2b_order_number
            (
              id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
              custom_ordernumber VARCHAR(255) NOT NULL,
              product_details_id INT(11) UNSIGNED NOT NULL,
              context_owner_id INT(11) NOT NULL,
              CONSTRAINT FK_b2b_product_number_s_articles_details_id FOREIGN KEY (product_details_id) REFERENCES s_articles_details (id) ON DELETE CASCADE,
              CONSTRAINT FK_b2b_order_number_context_owner_id FOREIGN KEY (context_owner_id) REFERENCES b2b_store_front_auth (id) ON DELETE CASCADE
            );'
        );
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
