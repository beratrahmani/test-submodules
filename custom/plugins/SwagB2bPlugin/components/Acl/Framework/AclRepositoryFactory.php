<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Doctrine\DBAL\Connection;

/**
 * Register ACLTables and retrieve repositories based on a subject table.
 */
class AclRepositoryFactory
{
    /**
     * @var AclTable[]
     */
    private $tables;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param AclTable[] $tables
     * @param Connection $connection
     */
    public function __construct(array $tables, Connection $connection)
    {
        $this->tables = $tables;
        $this->connection = $connection;
    }

    /**
     * @param string $subjectTableName
     * @throws \Shopware\B2B\Acl\Framework\AclUnsupportedContextException
     * @return AclRepository
     */
    public function createRepository(string $subjectTableName): AclRepository
    {
        $tables = [];
        foreach ($this->tables as $table) {
            if ($subjectTableName !== $table->getSubjectTableName()) {
                continue;
            }

            $tables[] = $table;
        }

        if (count($tables)) {
            return new AclRepository($tables, $this->connection);
        }

        throw new AclUnsupportedContextException('No table found for selector "' . $subjectTableName . '"');
    }
}
