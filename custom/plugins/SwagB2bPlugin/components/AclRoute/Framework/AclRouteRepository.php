<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Framework;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Acl\Framework\AclUnsupportedContextException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Repository\SearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class AclRouteRepository
{
    const TABLE_NAME = 'b2b_acl_route';
    const PRIVILEGE_TABLE_NAME = 'b2b_acl_route_privilege';
    const PRIVILEGE_TABLE_ALIAS = 'privilege';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var AclRepository
     */
    private $aclRepository;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param AclRepository $aclRepository
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        AclRepository $aclRepository
    ) {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
        $this->aclRepository = $aclRepository;
    }

    /**
     * @param string $controllerName
     * @param string $actionName
     * @throws NotFoundException
     * @return int
     */
    public function fetchPrivilegeIdByControllerAndActionName(string $controllerName, string $actionName): int
    {
        $id = (int) $this->connection->fetchColumn(
            'SELECT privilege_id FROM ' . self::TABLE_NAME . ' WHERE controller = :controllerName AND action = :actionName',
            [
                'controllerName' => $controllerName,
                'actionName' =>$actionName,
            ]
        );

        if (!$id) {
            throw new NotFoundException(sprintf('No record found for %s::%s', $controllerName, $actionName));
        }

        return $id;
    }

    /**
     * @param array $mapping
     * @param int $subjectId
     * @return array
     */
    public function fetchMappedRouteIds(array $mapping, int $subjectId): array
    {
        $route = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::PRIVILEGE_TABLE_NAME, self::PRIVILEGE_TABLE_ALIAS)
            ->where('id = :id')
            ->setParameter('id', $subjectId)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if (!$route) {
            throw new \InvalidArgumentException(sprintf('No route found for id "%d"', $subjectId));
        }

        if (!isset($mapping[$route['privilege_type']])) {
            throw new NotFoundException('No mapping for route found');
        }

        $routeIds = $this->connection->createQueryBuilder()
            ->select('id')
            ->from(self::PRIVILEGE_TABLE_NAME, self::PRIVILEGE_TABLE_ALIAS)
            ->where('resource_name = :resourceName')
            ->andWhere('privilege_type IN (:privilegeTypes)')
            ->setParameter('resourceName', $route['resource_name'])
            ->setParameter(
                'privilegeTypes',
                $mapping[$route['privilege_type']],
                Connection::PARAM_INT_ARRAY
            )
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        if (!$routeIds) {
            throw new NotFoundException('No mapped routes found');
        }

        return array_map('intval', $routeIds);
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @return array
     */
    public function fetchControllerList(OwnershipContext $ownershipContext): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::PRIVILEGE_TABLE_NAME, self::PRIVILEGE_TABLE_ALIAS)
            ->orderBy(self::PRIVILEGE_TABLE_ALIAS . '.resource_name');

        $statement = $query->execute();
        $aclData = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $aclRoutes = [];
        foreach ($aclData as $aclEntry) {
            $aclRoutes[] = (new AclRouteEntity())->fromDatabaseArray($aclEntry);
        }

        return $aclRoutes;
    }

    /**
     * @param SearchStruct $contactSearchStruct
     * @param OwnershipContext $ownershipContext
     * @return int
     */
    public function fetchTotalCount(SearchStruct $contactSearchStruct, OwnershipContext $ownershipContext): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::PRIVILEGE_TABLE_NAME, self::PRIVILEGE_TABLE_ALIAS);

        $this->dbalHelper->applyFilters($contactSearchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @return array
     */
    public function fetchAllActionIds(OwnershipContext $ownershipContext): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select('id')
            ->from(self::PRIVILEGE_TABLE_NAME, self::PRIVILEGE_TABLE_ALIAS)
            ->orderBy(self::PRIVILEGE_TABLE_ALIAS . '.resource_name');

        $this->applyGrantableAcl($ownershipContext, $query);

        $ids = $query->execute()
        ->fetchAll(\PDO::FETCH_COLUMN);

        return array_map(function ($id) {
            return (int) $id;
        }, $ids);
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param string $controllerName
     * @return array
     */
    public function fetchActionIdsByControllerName(OwnershipContext $ownershipContext, string $controllerName): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select('id')
            ->from(self::PRIVILEGE_TABLE_NAME, self::PRIVILEGE_TABLE_ALIAS)
            ->where(self::PRIVILEGE_TABLE_ALIAS . '.resource_name = :controller')
            ->setParameter('controller', $controllerName);

        $this->applyGrantableAcl($ownershipContext, $query);

        $ids = $query->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        return array_map(function ($id) {
            return (int) $id;
        }, $ids);
    }

    /**
     * @internal
     *
     * @param OwnershipContext $context
     * @param QueryBuilder $query
     */
    protected function applyGrantableAcl(OwnershipContext $context, QueryBuilder $query)
    {
        try {
            $aclQuery = $this->aclRepository->getUnionizedSqlQuery($context);

            $query->innerJoin(
                self::PRIVILEGE_TABLE_ALIAS,
                '(' . $aclQuery->sql . ')',
                'acl_query',
                self::PRIVILEGE_TABLE_ALIAS . '.id = acl_query.referenced_entity_id AND acl_query.grantable = 1'
            );

            foreach ($aclQuery->params as $name => $value) {
                $query->setParameter($name, $value);
            }
        } catch (AclUnsupportedContextException $e) {
            // nth
        }
    }
}
