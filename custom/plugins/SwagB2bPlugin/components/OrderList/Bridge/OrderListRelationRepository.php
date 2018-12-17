<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Bridge;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\OrderList\Framework\OrderListRelationRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderListRelationRepository implements OrderListRelationRepositoryInterface
{
    const TABLE_NAME = 'b2b_line_item_reference';
    const TABLE_ALIAS = 'lineItemReference';
    const TABLE_NAME_LIST = 'b2b_line_item_list';
    const TABLE_ALIAS_LIST = 'lineItemList';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function fetchOrderListNameForListId(int $listId, OwnershipContext $ownershipContext): string
    {
        $queryBuilder = $this->createBaseQueryBuilder($ownershipContext);

        $orderList = $queryBuilder
            ->select(self::TABLE_ALIAS . '.order_list_name')
            ->andWhere(self::TABLE_ALIAS . '.list_id = :listId')
            ->andWhere(self::TABLE_ALIAS . '.order_list_name IS NOT NULL')
            ->setParameter('listId', $listId)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        if (count($orderList) === 0) {
            throw new NotFoundException('Order has no b2b order list');
        }

        return implode(', ', array_unique($orderList));
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function fetchOrderListNameForPositionNumber(
        int $listId,
        string $productNumber,
        OwnershipContext $ownershipContext
    ): string {
        $queryBuilder = $this->createBaseQueryBuilder($ownershipContext);

        $orderList = $queryBuilder
            ->select(self::TABLE_ALIAS . '.order_list_name')
            ->andWhere(self::TABLE_ALIAS . '.list_id = :listId')
            ->andWhere(self::TABLE_ALIAS . '.reference_number = :productNumber')
            ->andWhere(self::TABLE_ALIAS . '.order_list_name IS NOT NULL')
            ->andWhere(self::TABLE_ALIAS . '.mode = 0')
            ->setParameter('listId', $listId)
            ->setParameter('productNumber', $productNumber)
            ->execute()
            ->fetchColumn();

        if (!$orderList) {
            throw new NotFoundException('Position has no b2b order list');
        }

        return $orderList;
    }

    /**
     * @internal
     * @param OwnershipContext $ownershipContext
     * @return QueryBuilder
     */
    protected function createBaseQueryBuilder(OwnershipContext $ownershipContext): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, self::TABLE_NAME_LIST, self::TABLE_ALIAS_LIST, self::TABLE_ALIAS . '.list_id = ' . self::TABLE_ALIAS_LIST . '.id')
            ->where(self::TABLE_ALIAS_LIST . '.context_owner_id = :ownerId')
            ->setParameter('ownerId', $ownershipContext->contextOwnerId);
    }

    /**
     * Bridge only
     *
     * @param int $orderContextId
     * @param string $productNumber
     * @param string $orderListName
     */
    public function addOrderListNameToLineItemReference(int $orderContextId, string $productNumber, string $orderListName)
    {
        $this->connection->executeUpdate(
            'UPDATE
              b2b_line_item_reference lineItemReference
            INNER JOIN
              b2b_order_context orderContext ON orderContext.list_id = lineItemReference.list_id
            SET
              lineItemReference.order_list_name = :orderListName
            WHERE
              lineItemReference.reference_number = :productNumber AND orderContext.id = :orderContextId ',
            [
                'orderListName' => $orderListName,
                'orderContextId' => $orderContextId,
                'productNumber' => $productNumber,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addOrderListToCartAttribute(LineItemList $list, string $orderListName)
    {
        $sessionId = Shopware()->Session()->get('sessionId');

        $sqlInPlaceholders = [];
        $queryParams = [];

        foreach ($list->references as $reference) {
            $placeholder = 'productNumber' . $reference->id;
            $sqlInPlaceholders[] = ':' . $placeholder;
            $queryParams[$placeholder] = $reference->referenceNumber;
        }

        $queryParams['orderListName'] = $orderListName;
        $queryParams['sessionId'] = $sessionId;

        $this->connection->executeUpdate(
            'UPDATE
              s_order_basket_attributes cartAttr
            INNER JOIN
              s_order_basket cart
            ON
              cart.id = cartAttr.basketID
            SET
              cartAttr.b2b_order_list = :orderListName
            WHERE
              cart.sessionID = :sessionId
            AND
              cart.ordernumber IN (' . implode(', ', $sqlInPlaceholders) . ')',
            $queryParams
        );
    }
}
