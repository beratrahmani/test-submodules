<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Acl\Framework\AclDdlService;
use Shopware\B2B\Address\Framework\DependencyInjection\AddressFrameworkConfiguration;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493482191InstallAddressFlag implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493482191;
    }

    public function updateDatabase(Connection $connection)
    {
    }

    public function updateThroughServices(Container $container)
    {
        $attributeService = $container->get('shopware_attribute.crud_service');
        $attributeService->update('s_user_addresses_attributes', 'b2b_type', 'string');

        foreach (AddressFrameworkConfiguration::createAclTables() as $table) {
            AclDdlService::create()->createTable($table);
        }
    }
}
