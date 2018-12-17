<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Shopware\Models\Emotion\Emotion;
use Symfony\Component\DependencyInjection\Container;

class Migration1495005711CustomerGroupLandingPage implements MigrationStepInterface
{
    /**
     * @return int
     */
    public function getCreationTimeStamp(): int
    {
        return 1495005711;
    }

    /**
     * @param Connection $connection
     */
    public function updateDatabase(Connection $connection)
    {
    }

    /**
     * @param Container $container
     */
    public function updateThroughServices(Container $container)
    {
        $attributeService = $container->get('shopware_attribute.crud_service');
        $attributeService->update(
            's_core_customergroups_attributes',
            'b2b_landingpage',
            'single_selection',
            [
                'label' => 'Dashboard Landingpage',
                'supportText' => 'landingpage for customergroup, wich is shown on top of the dashboard',
                'helpText' => '',
                'displayInBackend' => true,
                'custom' => false,
                'entity' => Emotion::class,
            ]
        );
    }
}
