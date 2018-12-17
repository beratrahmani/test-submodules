<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Doctrine\DBAL\Connection;

/**
 * IO for ACL records. Contains methods for manipulation and retrieval of acl ownership relationships.
 */
class AclRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AclTable[]
     */
    private $aclTables;

    /**
     * @param AclTable[] $aclTables
     * @param Connection $connection
     */
    public function __construct(array $aclTables, Connection $connection)
    {
        $this->connection = $connection;
        $this->aclTables = $aclTables;
    }

    /**
     * @param object $context
     * @param int $subjectId
     * @param bool $grantable
     */
    public function allow($context, int $subjectId, bool $grantable = false)
    {
        $resolver = $this->getAssignableTableResolver($context);

        if ($this->isDirectlyAllowed($context, $subjectId)) {
            $this->connection->update(
                $resolver->getTableName(),
                [
                    'grantable' => (int) $grantable,
                ],
                [
                    'entity_id' => $resolver->getId(),
                    'referenced_entity_id' => $subjectId,
                ]
            );

            return;
        }

        $this->connection
            ->insert(
                $resolver->getTableName(),
                [
                    'entity_id' => $resolver->getId(),
                    'referenced_entity_id' => $subjectId,
                    'grantable' => (int) $grantable,
                ]
            );
    }

    /**
     * @param $context
     * @param array $subjectIds
     * @param bool $grantable
     */
    public function allowAll($context, array $subjectIds, bool $grantable = false)
    {
        foreach ($subjectIds as $entityId) {
            $this->allow($context, $entityId, $grantable);
        }
    }

    /**
     * @param object $context
     * @param int $subjectId
     */
    public function deny($context, int $subjectId)
    {
        $resolver = $this->getAssignableTableResolver($context);

        $this->connection
            ->delete(
                $resolver->getTableName(),
                [
                    'entity_id' => $resolver->getId(),
                    'referenced_entity_id' => $subjectId,
                ]
            );
    }

    /**
     * @param $context
     * @param int $subjectId
     * @return array
     */
    public function getAllAssignedIdsBySubjectId($context, int $subjectId): array
    {
        $resolver = $this->getAssignableTableResolver($context);
        $query = $this->connection->createQueryBuilder();

        $resolver->getQuery($query);
        $query->select('DISTINCT entity_id')
            ->where('referenced_entity_id = :subjectId')
            ->setParameter('subjectId', $subjectId);

        $rawResult = $query->execute()->fetchAll();

        $assignedIds = [];
        foreach ($rawResult as $row) {
            $assignedIds[] = (int) $row['entity_id'];
        }

        return $assignedIds;
    }

    /**
     * @param object $context
     * @param array $subjectIds
     */
    public function denyAll($context, array $subjectIds)
    {
        foreach ($subjectIds as $entityId) {
            $this->deny($context, $entityId);
        }
    }

    /**
     * @param object $context
     * @param $subjectId
     * @return bool
     */
    public function isAllowed($context, $subjectId): bool
    {
        $query = $this->getUnionizedSqlQuery($context);

        $statement = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('(' . $query->sql . ')', 'acl_table')
            ->where('acl_table.referenced_entity_id = :referencedEntityId')
            ->setParameters($query->params)
            ->setParameter('referencedEntityId', $subjectId)
            ->execute();

        return (bool) $statement->fetchColumn();
    }

    /**
     * @param object $context
     * @param $subjectId
     * @return bool
     */
    public function isGrantable($context, $subjectId): bool
    {
        $query = $this->getUnionizedSqlQuery($context);

        $statement = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('(' . $query->sql . ')', 'acl_table')
            ->where('acl_table.referenced_entity_id = :referencedEntityId')
            ->andWhere('acl_table.grantable = 1')
            ->setParameters($query->params)
            ->setParameter('referencedEntityId', $subjectId)
            ->execute();

        return (bool) $statement->fetchColumn();
    }

    /**
     * @param object $context
     * @return int[]
     */
    public function getAllAllowedIds($context): array
    {
        $query = $this->getUnionizedSqlQuery($context);

        $statement = $this->connection->createQueryBuilder()
            ->select('DISTINCT acl_table.referenced_entity_id')
            ->from('(' . $query->sql . ')', 'acl_table')
            ->setParameters($query->params)
            ->execute();

        $rawResult = $statement->fetchAll();

        $allowedIds = [];
        foreach ($rawResult as $row) {
            $allowedIds[] = (int) $row['referenced_entity_id'];
        }

        return $allowedIds;
    }

    /**
     * @param object $context
     * @return array
     */
    public function fetchAllGrantableIds($context): array
    {
        $query = $this->getUnionizedSqlQuery($context);

        $statement = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('(' . $query->sql . ')', 'acl_table')
            ->setParameters($query->params)
            ->execute();

        $aclSettings = $statement->fetchAll();

        $result = [];
        foreach ($aclSettings as $setting) {
            $result[$setting['referenced_entity_id']] = (bool) $setting['grantable'];
        }

        return $result;
    }

    /**
     * @param $context
     * @return array
     */
    public function fetchAllDirectlyIds($context): array
    {
        $query = $this->getDirectAssignableQuery($context);

        $statement = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('(' . $query->sql . ')', 'acl_table')
            ->setParameters($query->params)->execute();

        $aclSettings = $statement->fetchAll();

        $result = [];
        foreach ($aclSettings as $setting) {
            $result[$setting['referenced_entity_id']] = (bool) $setting['grantable'];
        }

        return $result;
    }

    /**
     * @param object $context
     * @throws \Shopware\B2B\Acl\Framework\AclUnsupportedContextException
     * @return AclQuery
     */
    public function getUnionizedSqlQuery($context): AclQuery
    {
        $sql = [];
        $params = [];

        foreach ($this->aclTables as $aclTable) {
            try {
                $resolver = $aclTable->getResolver($context);
            } catch (AclUnsupportedContextException $e) {
                continue;
            }

            $query = $resolver->getQuery($this->connection->createQueryBuilder());

            $sql[] = '(' . $query->sql . ')';
            $params = array_merge($params, $query->params);
        }

        if (!$sql) {
            throw new AclUnsupportedContextException('Could not find a single valid query for this context');
        }

        $unionQuery = (new AclQuery())->fromPrimitives(implode(' UNION DISTINCT ', $sql), $params);

        return (new AclQuery())
            ->fromPrimitives('SELECT referenced_entity_id, MAX(grantable) AS grantable 
                                FROM (' . $unionQuery->sql . ') AS query 
                                GROUP BY referenced_entity_id', $unionQuery->params);
    }

    /**
     * @internal
     * @param object $context
     * @param $subjectId
     * @return bool
     */
    protected function isDirectlyAllowed($context, $subjectId): bool
    {
        $query = $this->getDirectAssignableQuery($context);

        $statement = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('(' . $query->sql . ')', 'acl_table')
            ->where('acl_table.referenced_entity_id = :referencedEntityId')
            ->setParameters($query->params)
            ->setParameter('referencedEntityId', $subjectId)
            ->execute();

        return (bool) $statement->fetchColumn();
    }

    /**
     * @param $context
     * @throws \Shopware\B2B\Acl\Framework\AclUnsupportedContextException
     * @return AclTableResolverFacade
     */
    public function getAssignableTableResolver($context): AclTableResolverFacade
    {
        foreach ($this->aclTables as $table) {
            try {
                return $table->getMainResolver($context);
            } catch (AclUnsupportedContextException $e) {
                // nth
            }
        }

        throw new AclUnsupportedContextException('No applying context found for ' . get_class($context));
    }

    /**
     * @param $context
     * @throws \Shopware\B2B\Acl\Framework\AclUnsupportedContextException
     * @return AclQuery
     */
    public function getDirectAssignableQuery($context): AclQuery
    {
        return $this->getAssignableTableResolver($context)
            ->getQuery($this->connection->createQueryBuilder());
    }
}
