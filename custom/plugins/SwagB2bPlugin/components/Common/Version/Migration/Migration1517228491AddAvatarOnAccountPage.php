<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1517228491AddAvatarOnAccountPage implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1517228491;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
ALTER TABLE b2b_store_front_auth
  ADD media_id INT NULL;
CREATE INDEX b2b_store_front_auth_media_id_idx
  ON b2b_store_front_auth (media_id);
ALTER TABLE b2b_store_front_auth
  ADD CONSTRAINT FK_b2b_store_front_auth_s_media_id
FOREIGN KEY (media_id) REFERENCES s_media (id);


ALTER TABLE b2b_store_front_auth drop COLUMN avatar;');

        $connection->exec('
INSERT INTO s_media_album (name, position) VALUES (\'B2b\', 15);
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function updateThroughServices(Container $container)
    {
        $attributeService = $container->get('shopware_attribute.crud_service');

        $attributeService->delete('s_user_attributes', 'b2b_sales_representative_media_id');
    }
}
