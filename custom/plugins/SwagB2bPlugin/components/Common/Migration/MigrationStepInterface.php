<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Migration;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Container;

interface MigrationStepInterface
{
    /**
     * @return int
     */
    public function getCreationTimeStamp(): int;

    /**
     * Update MySQL Tables here
     *
     * @param Connection $connection
     */
    public function updateDatabase(Connection $connection);

    /**
     * Update through Shopware Container base services here (eg. Attributes) or call static B2B Services (ACL)
     *
     * @param Container $container
     */
    public function updateThroughServices(Container $container);
}
