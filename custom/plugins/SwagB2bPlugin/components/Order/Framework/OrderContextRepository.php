<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\MysqlRepository;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderContextRepository
{
    const TABLE_NAME = 'b2b_order_context';

    const TABLE_ALIAS = 'orderContext';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $auditLogReferences = [
        'orderContextId' => self::TABLE_NAME,
        'listId' => LineItemListRepository::TABLE_NAME,
        'itemId' => LineItemReferenceRepository::TABLE_NAME,
    ];

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param int $id
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Repository\NotFoundException
     * @return OrderContext
     */
    public function fetchOneOrderContextById(int $id, OwnershipContext $ownershipContext): OrderContext
    {
        $rawOrderContextData = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->andWhere(self::TABLE_ALIAS . '.auth_id IN (SELECT DISTINCT id FROM b2b_store_front_auth WHERE '
                     . self::TABLE_ALIAS . '.auth_id = :authId OR context_owner_id = :authId)')
            ->setParameter('authId', $ownershipContext->contextOwnerId)
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        return $this->createOrderContextEntity($id, $rawOrderContextData);
    }

    /**
     * @param string $orderNumber
     * @throws \Shopware\B2B\Common\Repository\NotFoundException
     * @return OrderContext
     */
    public function fetchOneOrderContextByOrderNumber(string $orderNumber): OrderContext
    {
        $rawOrderContextData = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.ordernumber = :orderNumber')
            ->setParameter('orderNumber', $orderNumber)
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if (!$rawOrderContextData) {
            throw new NotFoundException('Could not find order context with number "' . $orderNumber . '"".');
        }

        return $this->createOrderContextEntity((int) $rawOrderContextData['id'], $rawOrderContextData);
    }

    /**
     * @param OrderContext $orderContext
     */
    public function addOrderContext(OrderContext $orderContext)
    {
        $this->connection
            ->insert('b2b_order_context', $orderContext->toDatabaseArray());

        $orderContext->id = (int) $this->connection->lastInsertId();
    }

    /**
     * @param OrderContext $orderContext
     */
    public function syncFinishOrder(OrderContext $orderContext)
    {
        $this->updateContext($orderContext);

        $this->connection->update(
            'b2b_order_context',
            ['cleared_at' => (new \DateTime())->format(MysqlRepository::MYSQL_DATETIME_FORMAT), ],
            ['id' => $orderContext->id]
        );
    }

    /**
     * @param int $listId
     * @return array
     */
    public function fetchAuditLogReferencesByListId(int $listId): array
    {
        $rawReferenceData = (array) $this->connection->createQueryBuilder()
            ->select([
                'list.id AS listId',
                'orderContext.id AS orderContextId',
            ])
            ->from('b2b_line_item_list', 'list')
            ->leftJoin('list', 'b2b_order_context', 'orderContext', 'list.id = orderContext.list_id')
            ->where('list.id = :listId')
            ->setParameter('listId', $listId)
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        return $this->mapAuditLogReferences($rawReferenceData);
    }

    /**
     * @param int $lineItemId
     * @return array
     */
    public function fetchAuditLogReferencesByLineItemId(int $lineItemId): array
    {
        $rawReferenceData = (array) $this->connection->createQueryBuilder()
            ->select([
                'lineItem.id AS itemId',
                'list.id AS listId',
                'orderContext.id AS orderContextId',
            ])
            ->from('b2b_line_item_reference', 'lineItem')
            ->leftJoin('lineItem', 'b2b_line_item_list', 'list', 'lineItem.list_id = list.id')
            ->leftJoin('list', 'b2b_order_context', 'orderContext', 'list.id = orderContext.list_id')
            ->where('lineItem.id = :lineItemId')
            ->setParameter('lineItemId', $lineItemId)
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        return $this->mapAuditLogReferences($rawReferenceData);
    }

    /**
     * @param int $orderContextId
     * @return array
     */
    public function fetchAuditLogReferencesByContextId(int $orderContextId): array
    {
        $rawReferenceData = (array) $this->connection->createQueryBuilder()
            ->select([
                'orderContext.id AS orderContextId',
                'list.id AS listId',
            ])
            ->from('b2b_order_context', 'orderContext')
            ->leftJoin('orderContext', 'b2b_line_item_list', 'list', 'list.id = orderContext.list_id')
            ->where('orderContext.id = :orderContextId')
            ->setParameter('orderContextId', $orderContextId)
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        return $this->mapAuditLogReferences($rawReferenceData);
    }

    /**
     * @internal
     * @param array $rawData
     * @return array
     */
    protected function mapAuditLogReferences(array $rawData): array
    {
        $auditLogReferences = [];

        foreach ($this->auditLogReferences as $canonicalFieldName => $tableName) {
            if (!isset($rawData[$canonicalFieldName])) {
                continue;
            }

            $auditLogReferences[$tableName] = (int) $rawData[$canonicalFieldName];
        }

        return $auditLogReferences;
    }

    /**
     * @internal
     * @param int $id
     * @param array|false $rawOrderContextData
     * @throws \Shopware\B2B\Common\Repository\NotFoundException
     * @return OrderContext
     */
    protected function createOrderContextEntity(int $id, $rawOrderContextData): OrderContext
    {
        if (!$rawOrderContextData) {
            throw new NotFoundException('No context found with id "' . $id . '"');
        }

        $orderContext = new OrderContext();
        $orderContext->fromDatabaseArray($rawOrderContextData);

        return $orderContext;
    }

    /**
     * @param OrderContext $orderContext
     */
    public function updateContext(OrderContext $orderContext)
    {
        $this->connection->update(
            self::TABLE_NAME,
            $orderContext->toDatabaseArray(),
            ['id' => $orderContext->id]
        );
    }
}
