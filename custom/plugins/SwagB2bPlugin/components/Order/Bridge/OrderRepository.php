<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderEntity;
use Shopware\B2B\Order\Framework\OrderRepositoryInterface;
use Shopware\B2B\Order\Framework\OrderSearchStruct;
use Shopware\B2B\Order\Framework\ShopOrderRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderRepository implements OrderRepositoryInterface
{
    const TABLE_NAME = 'b2b_order_context';

    const TABLE_ALIAS = 'orderContext';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var ShopOrderRepositoryInterface
     */
    private $shopOrderRepository;

    /**
     * @var LineItemListRepository
     */
    private $lineItemListRepository;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param ShopOrderRepositoryInterface $shopOrderRepository
     * @param LineItemListRepository $lineItemListRepository
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        ShopOrderRepositoryInterface $shopOrderRepository,
        LineItemListRepository $lineItemListRepository
    ) {
        $this->connection = $connection;
        $this->shopOrderRepository = $shopOrderRepository;
        $this->dbalHelper = $dbalHelper;
        $this->lineItemListRepository = $lineItemListRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchLists(OwnershipContext $ownershipContext, OrderSearchStruct $orderSearchStruct, CurrencyContext $currencyContext): array
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->addSelect('shopOrderStates.name as status')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_core_states', 'shopOrderStates', self::TABLE_ALIAS . '.status_id = shopOrderStates.id')
            ->where('orderContext.auth_id IN (SELECT DISTINCT id FROM b2b_store_front_auth WHERE orderContext.auth_id = :authId OR context_owner_id = :authId)')
            ->setParameter('authId', $ownershipContext->authId);

        if (!$orderSearchStruct->orderBy) {
            $orderSearchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $orderSearchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($orderSearchStruct, $queryBuilder);

        $rawOrders = $queryBuilder
            ->execute()
            ->fetchAll();

        $orders = [];
        foreach ($rawOrders as $rawOrder) {
            $order = new OrderEntity();
            $order->fromDatabaseArray($rawOrder);
            $order->list = $this->lineItemListRepository->fetchOneListById($order->listId, $currencyContext, $ownershipContext);
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchTotalCount(OwnershipContext $ownershipContext, OrderSearchStruct $orderSearchStruct): int
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->addSelect('shopOrderStates.name as status')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_core_states', 'shopOrderStates', self::TABLE_ALIAS . '.status_id = shopOrderStates.id')
            ->where('orderContext.auth_id IN (SELECT DISTINCT id FROM b2b_store_front_auth WHERE orderContext.auth_id = :authId OR context_owner_id = :authId)')
            ->setParameter('authId', $ownershipContext->authId);

        $this->dbalHelper->applyFilters($orderSearchStruct, $queryBuilder);

        return (int) $queryBuilder
            ->execute()
            ->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchOrderById(
        int $orderContextId,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): OrderEntity {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->addSelect('shopOrderStates.name as status')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_core_states', 'shopOrderStates', self::TABLE_ALIAS . '.status_id = shopOrderStates.id')
            ->where(self::TABLE_ALIAS . '.id=:id')
            ->andWhere(self::TABLE_ALIAS . '.auth_id IN (SELECT DISTINCT id FROM b2b_store_front_auth WHERE ' . self::TABLE_ALIAS . '.auth_id = :contextOwner OR context_owner_id = :contextOwner)')
            ->setParameter('id', $orderContextId)
            ->setParameter('contextOwner', $ownershipContext->contextOwnerId);

        $rawOrder = $queryBuilder
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if (!$rawOrder) {
            throw new NotFoundException(sprintf('orderContext with id %d not found', $orderContextId));
        }

        $order = new OrderEntity();
        $order->fromDatabaseArray($rawOrder);
        $order->list = $this->lineItemListRepository->fetchOneListById($order->listId, $currencyContext, $ownershipContext);

        return $order;
    }

    /**
     * @param int $listId
     * @throws NotFoundException
     * @return OrderContext
     */
    public function fetchOrderContextByListId(int $listId): OrderContext
    {
        $data = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('b2b_order_context', 'orderContext')
            ->where('orderContext.list_id = :listId')
            ->setParameter('listId', $listId)
            ->execute()
            ->fetch();

        if (!$data) {
            throw new NotFoundException(sprintf('orderContext by listId %d not found', $listId));
        }

        $orderContext = new OrderContext();
        $orderContext->fromDatabaseArray($data);

        return $orderContext;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderCommentByOrderContextId(int $orderContextId, string $comment)
    {
        $this->shopOrderRepository->setOrderCommentByOrderContextId($orderContextId, $comment);
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
        return [
            'ordernumber',
            'order_reference',
            'requested_delivery_date',
        ];
    }

    /**
     * @return array
     */
    public function getAdditionalSearchResourceAndFields(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAuthIdFromOrderById(int $orderId): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.auth_id')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :orderId')
            ->setParameter('orderId', $orderId);

        $authId = $query->execute()->fetchColumn();

        if (!$authId) {
            throw new NotFoundException(sprintf('Order not found for %s', $orderId));
        }

        return (int) $authId;
    }
}
