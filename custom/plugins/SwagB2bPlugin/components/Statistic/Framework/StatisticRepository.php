<?php declare(strict_types=1);

namespace Shopware\B2B\Statistic\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Filter\DateRangeFilter;
use Shopware\B2B\Common\Filter\EqualsFilter;
use Shopware\B2B\Common\Filter\Filter;
use Shopware\B2B\Common\Filter\FilterSubQueryWithEquals;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Contact\Framework\ContactEntity;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\Role\Framework\RoleEntity;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class StatisticRepository implements GridRepository
{
    const TABLE_ALIAS = 'orderContext';

    const TABLE_NAME = 'b2b_order_context';

    /**
     * @var array
     */
    private $groupByFunctions = [
        'week' => 'WEEKOFYEAR',
        'month' => 'MONTH',
        'year' => 'YEAR',
    ];

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        AuthenticationService $authenticationService
    ) {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param StatisticSearchStruct $searchStruct
     * @return StatisticAggregate[]
     */
    public function fetchGroupedList(OwnershipContext $ownershipContext, StatisticSearchStruct $searchStruct): array
    {
        $groupByFunction = $this->groupByFunctions[$searchStruct->groupBy];

        $query = $this->connection->createQueryBuilder()
            ->select([
                'list.id as listId',
                $groupByFunction . '(orderContext.created_at) AS createdAtGrouping',
                'YEAR(orderContext.created_at) AS createdAtYear',
                'SUM(list.amount) AS orderAmount',
                'SUM(list.amount_net) AS orderAmountNet',
                'COUNT(orderContext.id) AS orders',
                'SUM(reference.itemCount) AS itemCount',
                'SUM(reference.itemQuantityCount) AS itemQuantityCount',
            ])
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 'b2b_line_item_list', 'list', 'list.id = orderContext.list_id')
            ->innerJoin(
                'list',
                '(
                    SELECT 
                        list_id, 
                        COUNT(id) as itemCount,
                        SUM(quantity) as itemQuantityCount 
                    FROM b2b_line_item_reference
                    GROUP BY list_id
                )',
                'reference',
                'list.id = reference.list_id'
            )
            ->where(self::TABLE_ALIAS . '.auth_id IN (SELECT id FROM b2b_store_front_auth WHERE b2b_store_front_auth.id = :authId OR context_owner_id = :authId)')
            ->andWhere('orderContext.status_id != -4')
            ->groupBy('createdAtYear')
            ->addGroupBy('createdAtGrouping')
            ->orderBy('orderContext.created_at')
            ->setParameter('authId', $ownershipContext->authId);

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $statement = $query->execute();

        $statistics = [];
        while (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $statistics[] = (new StatisticAggregate())->fromDatabaseArray($row);
        }

        return $statistics;
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param StatisticSearchStruct $searchStruct
     * @return Statistic[]
     */
    public function fetchList(OwnershipContext $ownershipContext, StatisticSearchStruct $searchStruct): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select([
                'orderContext.created_at',
                'orderContext.cleared_at',
                'orderContext.ordernumber',
                'list.amount',
                'list.amount_net',
                'itemCount',
                'itemQuantityCount',
                'list.id as listId',
                'orderContext.id AS orderContextId',
                'orderContext.auth_id AS auth_id',
                'shopOrderStates.name as status',
            ])
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_core_states', 'shopOrderStates', 'orderContext.status_id = shopOrderStates.id')
            ->innerJoin(self::TABLE_ALIAS, 'b2b_line_item_list', 'list', 'list.id = orderContext.list_id')
            ->innerJoin(
                'list',
                '(
                    SELECT 
                        list_id, 
                        COUNT(id) as itemCount, 
                        SUM(quantity) as itemQuantityCount 
                    FROM b2b_line_item_reference
                    GROUP BY list_id
                )',
                'reference',
                'list.id = reference.list_id'
            )
            ->where(self::TABLE_ALIAS . '.auth_id IN (SELECT id FROM b2b_store_front_auth WHERE b2b_store_front_auth.id = :authId OR context_owner_id = :authId)')
            ->orderBy('orderContext.created_at', 'DESC')
            ->setParameter('authId', $ownershipContext->authId);

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $statement = $query->execute();

        $statistics = [];
        while (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            try {
                $identity = $this->authenticationService->getIdentityByAuthId((int) $row['auth_id']);
                $row['contact'] = $identity->getPostalSettings();
            } catch (NotFoundException $e) {
                //nth
            }
            $statistics[] = (new Statistic())->fromDatabaseArray($row);
        }

        return $statistics;
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param StatisticSearchStruct $searchStruct
     * @return int
     */
    public function fetchTotalCount(OwnershipContext $ownershipContext, StatisticSearchStruct $searchStruct): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_core_states', 'shopOrderStates', 'orderContext.status_id = shopOrderStates.id')
            ->innerJoin(self::TABLE_ALIAS, 'b2b_line_item_list', 'list', 'list.id = orderContext.list_id')
            ->innerJoin(
                'list',
                '(
                    SELECT 
                        list_id, 
                        COUNT(id) as itemCount, 
                        SUM(quantity) as itemQuantityCount 
                    FROM b2b_line_item_reference
                    GROUP BY list_id
                )',
                'reference',
                'list.id = reference.list_id'
            )
            ->where(self::TABLE_ALIAS . '.auth_id IN (SELECT id FROM b2b_store_front_auth WHERE b2b_store_front_auth.id = :authId OR context_owner_id = :authId)')
            ->orderBy('orderContext.created_at', 'DESC')
            ->setParameter('authId', $ownershipContext->authId);

        $this->dbalHelper->applyFilters($searchStruct, $query);

        return (int) $query
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * @param OwnershipContext $context
     * @return ContactEntity[]
     */
    public function fetchStatisticContactList(OwnershipContext $context): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(ContactRepository::TABLE_NAME, ContactRepository::TABLE_ALIAS)
            ->where(ContactRepository::TABLE_ALIAS . '.auth_id IN (SELECT id FROM b2b_store_front_auth WHERE b2b_store_front_auth.id = :authId OR context_owner_id = :authId)')
            ->andWhere('0 != (SELECT COUNT(*) FROM b2b_order_context WHERE auth_id = contact.auth_id)')
            ->orderBy(ContactRepository::TABLE_ALIAS . '.lastname', 'ASC')
            ->setParameter('authId', $context->authId);

        $statement = $query->execute();
        $contactsData = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $contacts = [];
        foreach ($contactsData as $contactData) {
            $contacts[] = (new ContactEntity())->fromDatabaseArray($contactData);
        }

        return $contacts;
    }

    /**
     * @param OwnershipContext $context
     * @return RoleEntity[]
     */
    public function fetchStatisticRoleList(OwnershipContext $context): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(RoleRepository::TABLE_ROLE_NAME, RoleRepository::TABLE_ROLE_ALIAS)
            ->where(RoleRepository::TABLE_ROLE_ALIAS . '.context_owner_id = :contextOwnerId')
            ->andWhere(RoleRepository::TABLE_ROLE_ALIAS . '.id IN (SELECT role_id FROM b2b_order_context orderContext INNER JOIN b2b_debtor_contact contact ON orderContext.auth_id = contact.auth_id INNER JOIN b2b_role_contact role ON role.debtor_contact_id = contact.id)')
            ->orderBy(RoleRepository::TABLE_ROLE_ALIAS . '.name', 'ASC')
            ->setParameter('contextOwnerId', $context->contextOwnerId);

        $statement = $query->execute();
        $rawRoles = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $roles = [];
        foreach ($rawRoles as $rawRole) {
            $roles[] = (new RoleEntity())->fromDatabaseArray($rawRole);
        }

        return $roles;
    }

    /**
     * @param OwnershipContext $context
     * @return array
     */
    public function fetchStatisticStatesList(OwnershipContext $context): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.id, shopOrderStates.id, shopOrderStates.name')
            ->from('s_core_states', 'shopOrderStates')
            ->innerJoin(
                'shopOrderStates',
                self::TABLE_NAME,
                self::TABLE_ALIAS,
                self::TABLE_ALIAS . '.status_id = shopOrderStates.id'
            )
            ->where(self::TABLE_ALIAS . '.auth_id IN (SELECT id FROM b2b_store_front_auth WHERE b2b_store_front_auth.id = :authId OR context_owner_id = :authId)')
            ->orderBy('shopOrderStates.name', 'ASC')
            ->setParameter('authId', $context->authId);

        $statement = $query->execute();
        $rawStates = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $states = [];
        foreach ($rawStates as $rawState) {
            if (!in_array($rawState, $states, true)) {
                $states[] = $rawState;
            }
        }

        return $states;
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return DateRangeFilter
     */
    public function createDateRangeFilter(\DateTime $from, \DateTime $to): DateRangeFilter
    {
        return new DateRangeFilter($this->getMainTableAlias(), 'created_at', $from, $to);
    }

    /**
     * @param int $authId
     * @return EqualsFilter
     */
    public function createEqualsAuthorFilter(int $authId): EqualsFilter
    {
        return new EqualsFilter($this->getMainTableAlias(), 'auth_id', $authId);
    }

    /**
     * @param int $stateId
     * @return Filter
     */
    public function createEqualsStatesFilter(int $stateId): Filter
    {
        return new EqualsFilter($this->getMainTableAlias(), 'status_id', $stateId);
    }

    /**
     * @param int $roleId
     * @return FilterSubQueryWithEquals
     */
    public function createEqualsRoleFilter(int $roleId): FilterSubQueryWithEquals
    {
        return new FilterSubQueryWithEquals(
            self::TABLE_ALIAS . '.auth_id IN (
                SELECT DISTINCT %1$s_contact.auth_id 
                FROM b2b_debtor_contact %1$s_contact 
                INNER JOIN b2b_role_contact %1$s ON %1$s_contact.id = %1$s.debtor_contact_id 
                WHERE %2$s
            )',
            'role_id',
            $roleId
        );
    }

    /**
     * @return string query alias for filter construction
     */
    public function getMainTableAlias(): string
    {
        return self::TABLE_ALIAS;
    }

    /**
     * @return string[]
     */
    public function getFullTextSearchFields(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAdditionalSearchResourceAndFields(): array
    {
        return [];
    }
}
