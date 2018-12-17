<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Acl\Framework\AclReadHelper;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderListRepository implements GridRepository
{
    const TABLE_NAME = 'b2b_order_list';

    const TABLE_ALIAS = 'orderList';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var LineItemListRepository
     */
    private $lineItemListRepository;

    /**
     * @var AclReadHelper
     */
    private $aclReadHelper;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param LineItemListRepository $lineItemListRepository
     * @param AclReadHelper $aclReadHelper
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        LineItemListRepository $lineItemListRepository,
        AclReadHelper $aclReadHelper
    ) {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
        $this->lineItemListRepository = $lineItemListRepository;
        $this->aclReadHelper = $aclReadHelper;
    }

    /**
     * @param OrderListSearchStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     * @param CurrencyContext $currencyContext
     * @return array
     */
    public function fetchList(
        OrderListSearchStruct $searchStruct,
        OwnershipContext $ownershipContext,
        CurrencyContext $currencyContext
    ): array {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.context_owner_id = :contextOwnerId')
            ->setParameter('contextOwnerId', $ownershipContext->contextOwnerId);

        $this->aclReadHelper->applyAclVisibility($ownershipContext, $query);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper
            ->applySearchStruct($searchStruct, $query);

        $rawOrderListData = $query->execute()->fetchAll();

        $orderLists = [];
        foreach ($rawOrderListData as $orderListData) {
            $orderList = new OrderListEntity();
            $orderList->fromDatabaseArray($orderListData);
            $orderList->lineItemList = $this->lineItemListRepository
                ->fetchOneListById((int) $orderList->listId, $currencyContext, $ownershipContext);

            $orderLists[] = $orderList;
        }

        return $orderLists;
    }

    /**
     * @param OrderListSearchStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     * @return int
     */
    public function fetchTotalCount(OrderListSearchStruct $searchStruct, OwnershipContext $ownershipContext): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(' . self::TABLE_ALIAS . '.id)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.context_owner_id = :contextOwnerId')
            ->setParameter('contextOwnerId', $ownershipContext->contextOwnerId);

        $this->aclReadHelper->applyAclVisibility($ownershipContext, $query);

        $this->dbalHelper
            ->applyFilters($searchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @param int $id
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @throws NotFoundException
     * @return OrderListEntity
     */
    public function fetchOneById(int $id, CurrencyContext $currencyContext, OwnershipContext $ownershipContext): OrderListEntity
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->andWhere(self::TABLE_ALIAS . '.context_owner_id = :contextOwnerId')
            ->setParameter('id', $id)
            ->setParameter('contextOwnerId', $ownershipContext->contextOwnerId);

        $orderListData = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        if (!$orderListData) {
            throw new NotFoundException(sprintf('Order list not found for %s', $id));
        }

        $orderList = new OrderListEntity();
        $orderList->fromDatabaseArray($orderListData);
        $orderList->lineItemList = $this->lineItemListRepository
            ->fetchOneListById($orderList->listId, $currencyContext, $ownershipContext);

        return $orderList;
    }

    /**
     * @param OrderListEntity $orderList
     * @param OwnershipContext $ownershipContext
     * @throws CanNotInsertExistingRecordException
     * @return OrderListEntity
     */
    public function addOrderList(OrderListEntity $orderList, OwnershipContext $ownershipContext): OrderListEntity
    {
        if (!$orderList->isNew()) {
            throw new CanNotInsertExistingRecordException('The order list provided already exists');
        }

        $this->connection
            ->insert(
                self::TABLE_NAME,
                array_merge(
                    $orderList->toDatabaseArray(),
                    ['context_owner_id' => $ownershipContext->contextOwnerId]
                )
            );

        $orderList->id = (int) $this->connection->lastInsertId();

        return $orderList;
    }

    /**
     * @param OrderListEntity $orderList
     * @param OwnershipContext $ownershipContext
     * @throws CanNotUpdateExistingRecordException
     * @return OrderListEntity
     */
    public function updateOrderList(OrderListEntity $orderList, OwnershipContext $ownershipContext): OrderListEntity
    {
        if ($orderList->isNew()) {
            throw new CanNotUpdateExistingRecordException('Order list is not yet created.');
        }

        $this->connection
            ->update(
                self::TABLE_NAME,
                $orderList->toDatabaseArray(),
                [
                    'id' => $orderList->id,
                    'context_owner_id' => $ownershipContext->contextOwnerId,
                ]
            );

        return $orderList;
    }

    /**
     * @param OrderListEntity $orderList
     * @param OwnershipContext $ownershipContext
     * @throws CanNotRemoveExistingRecordException
     * @return OrderListEntity
     */
    public function removeOrderList(OrderListEntity $orderList, OwnershipContext $ownershipContext)
    {
        if ($orderList->isNew()) {
            throw new CanNotRemoveExistingRecordException('The order list provided does not exist');
        }

        $numDeleted = $this->connection->delete(
            self::TABLE_NAME,
            [
                'id' => $orderList->id,
                'context_owner_id' => $ownershipContext->contextOwnerId,
            ]
        );

        if (!$numDeleted) {
            throw new NotFoundException('The order list provided does not exist');
        }

        $this->lineItemListRepository->removeLineItemListById($orderList->listId, $ownershipContext);

        $orderList->id = null;
        $orderList->listId = null;

        return $orderList;
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
            'name',
        ];
    }

    /**
     * @return array
     */
    public function getAdditionalSearchResourceAndFields(): array
    {
        return [
            self::TABLE_ALIAS . '.list_id IN (SELECT %1$s.list_id FROM b2b_line_item_reference %1$s WHERE %1$s.list_id = orderList.list_id AND %2$s)' => ['reference_number'],
        ];
    }
}
