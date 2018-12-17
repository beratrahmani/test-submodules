<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Media\Media;
use Symfony\Component\DependencyInjection\Container;

class Migration1493816677AddSalesRepresentative implements MigrationStepInterface
{
    /**
     * @return int
     */
    public function getCreationTimeStamp(): int
    {
        return 1493816677;
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
        $attributeService->update('s_user_attributes', 'b2b_is_sales_representative', 'boolean');
        $attributeService->update(
            's_user_attributes',
            'b2b_sales_representative_media_id',
            'single_selection',
            [
                'label' => 'Sales Representative Image',
                'supportText' => 'Image to represent the sales representative in the top bar after client login',
                'helpText' => '',
                'displayInBackend' => true,
                'custom' => false,
                'entity' => Media::class,
            ]
        );
        $attributeService->update(
            's_user_attributes',
            'b2b_sales_representative_id',
            'single_selection',
            [
                'label' => 'Sales Representative',
                'supportText' => 'Selection of sales representative to determine the clients',
                'helpText' => '',
                'displayInBackend' => true,
                'custom' => false,
                'entity' => Customer::class,
            ]
        );
    }
}
