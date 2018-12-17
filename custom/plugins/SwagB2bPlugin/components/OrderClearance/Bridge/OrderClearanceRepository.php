<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Bridge;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Acl\Framework\AclUnsupportedContextException;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\MysqlRepository;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\Order\Framework\ShopOrderRepositoryInterface;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceEntity;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceEntityFactoryInterface;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceRepositoryInterface;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderClearanceRepository implements OrderClearanceRepositoryInterface
{
    const TABLE_NAME = 'b2b_order_context';

    const TABLE_ALIAS = 'b2bOrder';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var OrderClearanceEntityFactoryInterface
     */
    private $entityFactory;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var ShopOrderRepositoryInterface
     */
    private $shopOrderRepository;

    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

    /**
     * @var LineItemListRepository
     */
    private $lineItemListRepository;

    /**
     * @var AclRepository
     */
    private $contactAclRepository;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param ShopOrderRepositoryInterface $shopOrderRepository
     * @param OrderContextRepository $orderContextRepository
     * @param OrderClearanceEntityFactoryInterface $entityFactory
     * @param AuthenticationService $authenticationService
     * @param LineItemListRepository $lineItemListRepository
     * @param AclRepository $contactAclRepository
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        ShopOrderRepositoryInterface $shopOrderRepository,
        OrderContextRepository $orderContextRepository,
        OrderClearanceEntityFactoryInterface $entityFactory,
        AuthenticationService $authenticationService,
        LineItemListRepository $lineItemListRepository,
        AclRepository $contactAclRepository
    ) {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
        $this->entityFactory = $entityFactory;
        $this->authenticationService = $authenticationService;
        $this->shopOrderRepository = $shopOrderRepository;
        $this->orderContextRepository = $orderContextRepository;
        $this->lineItemListRepository = $lineItemListRepository;
        $this->contactAclRepository = $contactAclRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchOneByOrderContextId(
        int $orderContextId,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): OrderClearanceEntity {
        $order = $this->createBaseQueryBuilder()
            ->select(
                self::TABLE_ALIAS . '.*',
                'cs.name as status'
            )
            ->where(self::TABLE_ALIAS . '.id = :orderContextId')
            ->andWhere(self::TABLE_ALIAS . '.status_id = :status')
            ->andWhere('auth.context_owner_id = :contextOwner')
            ->setParameter(':orderContextId', $orderContextId)
            ->setParameter(':status', self::STATUS_ORDER_CLEARANCE)
            ->setParameter(':contextOwner', $ownershipContext->contextOwnerId)
            ->execute()
            ->fetch();

        if (!$order) {
            throw new NotFoundException(
                'Could not find an order with context id "' . $orderContextId . '" 
                 and status "' . self::STATUS_ORDER_CLEARANCE . '"'
            );
        }

        return $this->createOrder($order, (int) $order['auth_id'], $currencyContext, $ownershipContext);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAllOrderClearances(Identity $identity, OrderClearanceSearchStruct $searchStruct, CurrencyContext $currencyContext): array
    {
        $ownershipContext = $identity->getOwnershipContext();

        $queryBuilder = $this
            ->createBaseQueryBuilder()
            ->select([
                self::TABLE_ALIAS . '.*',
                self::TABLE_ALIAS . '.id as order_context_id',
                'cs.name as status',
            ])
            ->where(self::TABLE_ALIAS . '.status_id = :status')
            ->andWhere('auth.context_owner_id = :contextOwnerId')
            ->setParameter(':status', self::STATUS_ORDER_CLEARANCE)
            ->setParameter(':contextOwnerId', $ownershipContext->contextOwnerId);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper
            ->applySearchStruct($searchStruct, $queryBuilder);

        $this->applyContactAcl($ownershipContext, $queryBuilder);

        $rawOrderClearances = $queryBuilder->execute()->fetchAll();

        $orders = [];
        foreach ($rawOrderClearances as $rawOrderClearance) {
            $orders[] = $this->createOrder(
                $rawOrderClearance,
                (int) $rawOrderClearance['auth_id'],
                $currencyContext,
                $ownershipContext
            );
        }

        return $orders;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchTotalCount(Identity $identity, OrderClearanceSearchStruct $searchStruct): int
    {
        $ownershipContext = $identity->getOwnershipContext();

        $query = $this->createBaseQueryBuilder()
            ->select('COUNT(DISTINCT ' . self::TABLE_ALIAS . '.id)')
            ->where(self::TABLE_ALIAS . '.status_id = :status')
            ->andWhere('auth.context_owner_id = :contextOwnerId')
            ->setParameter(':status', self::STATUS_ORDER_CLEARANCE)
            ->setParameter(':contextOwnerId', $ownershipContext->contextOwnerId);

        $this->dbalHelper->applyFilters($searchStruct, $query);
        $this->applyContactAcl($ownershipContext, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * {@inheritdoc}
     */
    public function belongsOrderContextIdToDebtor(Identity $identity, int $orderContextId): bool
    {
        $contextOwnerId = $identity->getOwnershipContext()->contextOwnerId;

        return (bool) $this->createBaseQueryBuilder()
            ->select('COUNT(*)')
            ->where(self::TABLE_ALIAS . '.id = :orderContextId')
            ->andWhere('auth.context_owner_id = :contextOwnerId')
            ->setParameter(':orderContextId', $orderContextId)
            ->setParameter(':contextOwnerId', $contextOwnerId)
            ->execute()
            ->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function acceptOrder(int $orderContextId, string $comment, OwnershipContext $ownershipContext)
    {
        $this->setOrderStatusId(
            $orderContextId,
            self::STATUS_ORDER_OPEN,
            [
                'comment' => $comment,
                'cleared_at' => (new \DateTime())->format(MysqlRepository::MYSQL_DATETIME_FORMAT),
            ],
            $ownershipContext
        );
    }

    /**
     * {@inheritdoc}
     */
    public function declineOrder(int $orderContextId, string $comment, OwnershipContext $ownershipContext)
    {
        $this->setOrderStatusId(
            $orderContextId,
            self::STATUS_ORDER_DENIED,
            [
                'comment' => $comment,
                'declined_at' => (new \DateTime())->format(MysqlRepository::MYSQL_DATETIME_FORMAT),
            ],
            $ownershipContext
        );
    }

    /**
     * {@inheritdoc}
     */
    public function sendToOrderClearance(int $orderContextId, OwnershipContext $ownershipContext)
    {
        $this->setOrderStatusId($orderContextId, self::STATUS_ORDER_CLEARANCE, [], $ownershipContext);
    }

    /**
     * @internal
     * @param int $orderContextId
     * @param int $statusId
     * @param array $additionalData
     * @param OwnershipContext $ownershipContext
     */
    protected function setOrderStatusId(
        int $orderContextId,
        int $statusId,
        array $additionalData,
        OwnershipContext $ownershipContext
    ) {
        $success = (bool) $this->connection->update(
            self::TABLE_NAME,
            array_merge(
                ['status_id' => $statusId],
                $additionalData
            ),
            ['id' => $orderContextId]
        );

        if (!$success) {
            throw new CanNotUpdateExistingRecordException(
                "Could not update b2b order context status with context id {$orderContextId}"
            );
        }

        $this->shopOrderRepository
            ->updateStatus($orderContextId, $statusId, $ownershipContext);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteOrder(int $orderContextId, OwnershipContext $ownershipContext)
    {
        $orderContext = $this->orderContextRepository
            ->fetchOneOrderContextById($orderContextId, $ownershipContext);

        $this->connection->createQueryBuilder()
            ->delete(self::TABLE_NAME)
            ->where('id = :orderId')
            ->setParameter('orderId', $orderContext->id)
            ->execute();

        $this->connection->delete(
            'b2b_line_item_list',
            ['id' => $orderContext->listId]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMainTableAlias(): string
    {
        return self::TABLE_ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullTextSearchFields(): array
    {
        return [
            'ordernumber',
            'order_reference',
            'requested_delivery_date',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalSearchResourceAndFields(): array
    {
        return [];
    }

    /**
     * @internal
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function createBaseQueryBuilder()
    {
        return $this->connection->createQueryBuilder()
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_core_states', 'cs', self::TABLE_ALIAS . '.status_id = cs.id')
            ->innerJoin(self::TABLE_ALIAS, 'b2b_store_front_auth', 'auth', self::TABLE_ALIAS . '.auth_id = auth.id')
            ->leftJoin(self::TABLE_ALIAS, 'b2b_debtor_contact', 'contact', self::TABLE_ALIAS . '.auth_id = contact.auth_id');
    }

    /**
     * @internal
     * @param array $data
     * @param int $authId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return OrderClearanceEntity
     */
    protected function createOrder(
        array $data,
        int $authId,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): OrderClearanceEntity {
        $order = $this->entityFactory
            ->createOrderEntityFromDatabase($data, $ownershipContext);

        $order->list = $this->lineItemListRepository->fetchOneListById($order->listId, $currencyContext, $ownershipContext);
        try {
            $identity = $this->authenticationService->getIdentityByAuthId($authId);
        } catch (NotFoundException $e) {
            return $order;
        }

        $order->userPostalSettings = $identity->getPostalSettings();

        return $order;
    }

    /**
     * @internal
     * @param OwnershipContext $context
     * @param QueryBuilder $query
     */
    protected function applyContactAcl(OwnershipContext $context, QueryBuilder $query)
    {
        try {
            $aclQuery = $this->contactAclRepository->getUnionizedSqlQuery($context);

            $query->leftJoin(
                'contact',
                '(' . $aclQuery->sql . ')',
                'acl_query',
                'contact.id = acl_query.referenced_entity_id'
            );

            $query->andWhere('contact.id <=> acl_query.referenced_entity_id');

            foreach ($aclQuery->params as $name => $value) {
                $query->setParameter($name, $value);
            }
        } catch (AclUnsupportedContextException $e) {
            // nth
        }
    }
}
