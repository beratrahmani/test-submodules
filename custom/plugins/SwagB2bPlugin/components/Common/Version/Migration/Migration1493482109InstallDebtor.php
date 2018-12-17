<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493482109InstallDebtor implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493482109;
    }

    public function updateDatabase(Connection $connection)
    {
    }

    public function updateThroughServices(Container $container)
    {
        $attributeService = $container->get('shopware_attribute.crud_service');
        $attributeService->update('s_user_attributes', 'b2b_is_debtor', 'boolean');
    }
}
