<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Components\DependencyInjection\Container;

class MigrationRuntime
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $migrationTableName;

    /**
     * @param string $migrationTableName
     * @return MigrationRuntime
     */
    public static function create(string $migrationTableName): self
    {
        return new self(
            $migrationTableName,
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()
        );
    }

    /**
     * @param string $migrationTableName
     * @param Connection $connection
     * @param Container $container
     */
    public function __construct(string $migrationTableName, Connection $connection, Container $container)
    {
        $this->migrationTableName = $migrationTableName;
        $this->connection = $connection;
        $this->container = $container;
    }

    /**
     * @param MigrationStepInterface[] $migrationSteps
     */
    public function migrate(array $migrationSteps)
    {
        $this->ensureMigrationTableExists();

        foreach ($migrationSteps as $migrationStep) {
            if ($this->isExecuted($migrationStep)) {
                continue;
            }

            $migrationStep->updateDatabase($this->connection);
            $migrationStep->updateThroughServices($this->container);

            $this->setExecuted($migrationStep);
        }

        $this->container->get('models')->generateAttributeModels();
    }

    /**
     * @param MigrationStepInterface $migrationStep
     * @return bool
     */
    public function isExecuted(MigrationStepInterface $migrationStep): bool
    {
        $this->ensureMigrationTableExists();

        return (bool) $this->connection->fetchColumn(
            'SELECT COUNT(*) FROM `' . $this->migrationTableName . '` WHERE identifier=:identifier',
            ['identifier' => $migrationStep->getCreationTimeStamp()]
        );
    }

    /**
     * @internal
     * @param MigrationStepInterface $migrationStep
     */
    protected function setExecuted(MigrationStepInterface $migrationStep)
    {
        $this->connection->insert(
            $this->migrationTableName,
            [
                'identifier' => $migrationStep->getCreationTimeStamp(),
                'class' => get_class($migrationStep),
            ]
        );
    }

    /**
     * @internal
     */
    protected function ensureMigrationTableExists()
    {
        $this->connection->exec('
                CREATE TABLE IF NOT EXISTS `' . $this->migrationTableName . '` (
                    `identifier` INT(11) NOT NULL,
                    `class` VARCHAR(1000) NOT NULL COLLATE \'utf8_unicode_ci\',
                    PRIMARY KEY (`identifier`)
                )
                COLLATE=\'utf8_unicode_ci\'
                ENGINE=InnoDB;
        ');
    }
}
