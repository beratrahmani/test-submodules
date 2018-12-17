<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Bridge;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Repository\SearchStruct;
use Shopware\B2B\OrderNumber\Framework\OrderNumberEntity;
use Shopware\B2B\OrderNumber\Framework\OrderNumberRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderNumberRepository implements OrderNumberRepositoryInterface
{
    const TABLE_ALIAS = 'number';
    const TABLE_NAME = 'b2b_order_number';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     */
    public function __construct(Connection $connection, DbalHelper $dbalHelper)
    {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
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
            'custom_ordernumber',
        ];
    }

    /**
     * @return array
     */
    public function getAdditionalSearchResourceAndFields(): array
    {
        return ['%2$s' => [
                'details' => 'ordernumber',
            ],
        ];
    }

    /**
     * @param SearchStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     * @return array
     */
    public function fetchList(SearchStruct $searchStruct, OwnershipContext $ownershipContext): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([self::TABLE_ALIAS . '.id', 'custom_ordernumber', 'details.ordernumber', 'product_details_id', 'context_owner_id'])
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_articles_details', 'details', 'product_details_id = details.id')
            ->where(self::TABLE_ALIAS . '.context_owner_id = :ownerId')
            ->setParameter('ownerId', $ownershipContext->contextOwnerId);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'ASC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $statement = $query->execute();
        $orderNumberData = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $orderNumbers = [];
        foreach ($orderNumberData as $orderData) {
            $orderNumbers[] = (new OrderNumberEntity())->fromDatabaseArray($orderData);
        }

        return $orderNumbers;
    }

    /**
     * @param SearchStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     * @return int
     */
    public function fetchTotalCount(SearchStruct $searchStruct, OwnershipContext $ownershipContext): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_articles_details', 'details', 'product_details_id = details.id')
            ->where(self::TABLE_ALIAS . '.context_owner_id = :ownerId')
            ->setParameter('ownerId', $ownershipContext->contextOwnerId);

        $this->dbalHelper->applyFilters($searchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @return bool
     */
    public function isCustomOrderNumberAvailable(OrderNumberEntity $orderNumberEntity): bool
    {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.custom_ordernumber = :customOrderNumber')
            ->andWhere('id <> :orderNumberId')
            ->andWhere('context_owner_id = :ownerId')
            ->setParameter('customOrderNumber', $orderNumberEntity->customOrderNumber)
            ->setParameter('orderNumberId', $orderNumberEntity->id ?: 0)
            ->setParameter('ownerId', $orderNumberEntity->contextOwnerId);

        $unAvailable = (bool) $query->execute()->fetchColumn(0);

        return !$unAvailable;
    }

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @return OrderNumberEntity
     */
    public function updateOrderNumber(OrderNumberEntity $orderNumberEntity): OrderNumberEntity
    {
        if ($orderNumberEntity->isNew()) {
            throw new CanNotUpdateExistingRecordException('The ordernumber provided does not exist');
        }

        if (!$orderNumberEntity->productDetailsId) {
            $detailsId = $this->fetchDetailsId($orderNumberEntity);
            $orderNumberEntity->productDetailsId = $detailsId;
        }

        $this->connection->update(
            self::TABLE_NAME,
            $orderNumberEntity->toDatabaseArray(),
            [
                'id' => $orderNumberEntity->id,
                'context_owner_id' => $orderNumberEntity->contextOwnerId,
            ]
        );

        return $orderNumberEntity;
    }

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @return OrderNumberEntity
     */
    public function createOrderNumber(OrderNumberEntity $orderNumberEntity): OrderNumberEntity
    {
        if (!$orderNumberEntity->isNew()) {
            throw new CanNotInsertExistingRecordException('The custom ordernumber provided already exist.');
        }

        if (!$orderNumberEntity->productDetailsId) {
            $detailsId = $this->fetchDetailsId($orderNumberEntity);
            $orderNumberEntity->productDetailsId = $detailsId;
        }

        $this->connection->insert(
            self::TABLE_NAME,
            $orderNumberEntity->toDatabaseArray()
        );

        $orderNumberEntity->id = (int) $this->connection->lastInsertId();

        return $orderNumberEntity;
    }

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @throws NotFoundException
     * @return int
     */
    public function fetchDetailsId(OrderNumberEntity $orderNumberEntity): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('s_articles_details', 'details')
            ->where('ordernumber = :orderNumber')
            ->setParameter('orderNumber', $orderNumberEntity->orderNumber);

        $result = $query->execute()->fetchColumn(0);

        if ($result === false) {
            throw new NotFoundException('The ordernumber provided does not exist.');
        }

        return (int) $result;
    }

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @return OrderNumberEntity
     */
    public function removeOrderNumber(OrderNumberEntity $orderNumberEntity): OrderNumberEntity
    {
        if ($orderNumberEntity->isNew()) {
            throw new NotFoundException('The ordernumber provided does not exist');
        }

        $this->connection->delete(
            self::TABLE_NAME,
            [
                'id' => $orderNumberEntity->id,
                'context_owner_id' => $orderNumberEntity->contextOwnerId,
            ]
        );

        $orderNumberEntity->id = null;

        return $orderNumberEntity;
    }

    /**
     * @param OwnershipContext $context
     * @return array
     */
    public function fetchAllProductsForExport(OwnershipContext $context): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select([
                'details.ordernumber',
                self::TABLE_ALIAS . '.custom_ordernumber',
                'product_details_id',
                self::TABLE_ALIAS . '.id',
                self::TABLE_ALIAS . '.context_owner_id',
            ])
            ->from('s_articles_details', 'details')
            ->innerJoin('details', self::TABLE_NAME, self::TABLE_ALIAS, self::TABLE_ALIAS . '.product_details_id = details.id and context_owner_id = :ownerId')
            ->orderBy('details.ordernumber', 'ASC')
            ->setParameter('ownerId', $context->contextOwnerId);

        $statement = $query->execute();

        $orderNumberData = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $orderNumbers = [];
        foreach ($orderNumberData as $orderData) {
            $orderNumbers[] = (new OrderNumberEntity())->fromDatabaseArray($orderData);
        }

        return $orderNumbers;
    }

    /**
     * @param OwnershipContext $ownershipContext
     */
    public function clearOrderNumbers(OwnershipContext $ownershipContext)
    {
        $this->connection->createQueryBuilder()
            ->delete(self::TABLE_NAME)
            ->where('context_owner_id = :contextOwnerId')
            ->setParameter('contextOwnerId', $ownershipContext->contextOwnerId)
            ->execute();
    }

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @return bool
     */
    public function isOrderNumberUnique(OrderNumberEntity $orderNumberEntity): bool
    {
        $query = $this->connection->createQueryBuilder()
            ->select('count(*)')
            ->from('s_articles_details', 'details')
            ->innerJoin('details', self::TABLE_NAME, self::TABLE_ALIAS, self::TABLE_ALIAS . '.product_details_id = details.id')
            ->andWhere(self::TABLE_ALIAS . '.id <> :orderNumberId')
            ->andWhere('context_owner_id = :ownerId')
            ->andWhere('details.ordernumber = :orderNumber')
            ->setParameter('orderNumber', $orderNumberEntity->orderNumber)
            ->setParameter('orderNumberId', $orderNumberEntity->id ?: 0)
            ->setParameter('ownerId', $orderNumberEntity->contextOwnerId);

        $exists = (bool) $query->execute()->fetchColumn(0);

        return !$exists;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchCustomOrderNumbers(array $orderNumbers, OwnershipContext $context): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select(['ordernumber', 'coalesce(custom_ordernumber, ordernumber) as custom_ordernumber'])
            ->from('s_articles_details', 'details')
            ->leftJoin('details', self::TABLE_NAME, self::TABLE_ALIAS, self::TABLE_ALIAS . '.product_details_id = details.id and context_owner_id = :contextOwner')
            ->where('custom_ordernumber IN (:ordernumbers) or ordernumber in (:ordernumbers)')
            ->setParameter('ordernumbers', $orderNumbers, Connection::PARAM_STR_ARRAY)
            ->setParameter('contextOwner', $context->contextOwnerId);

        $customOrderNumbers = $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $customOrderNumbers;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchCustomOrderNumber(string $orderNumber, OwnershipContext $ownershipContext): string
    {
        $orderNumbers = $this->fetchCustomOrderNumbers([$orderNumber], $ownershipContext);
        $customOrderNumber = array_shift($orderNumbers);

        return $customOrderNumber ?: $orderNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchOriginalOrderNumbers(array $numbers, OwnershipContext $context): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select(['coalesce(custom_ordernumber, ordernumber) as custom_ordernumber', 'ordernumber'])
            ->from('s_articles_details', 'details')
            ->leftJoin('details', self::TABLE_NAME, self::TABLE_ALIAS, self::TABLE_ALIAS . '.product_details_id = details.id and context_owner_id = :contextOwner')
            ->where('ordernumber IN (:ordernumbers) or custom_ordernumber IN (:ordernumbers)')
            ->setParameter('ordernumbers', $numbers, Connection::PARAM_STR_ARRAY)
            ->setParameter('contextOwner', $context->contextOwnerId);

        $originalOrderNumbers = $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $originalOrderNumbers;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchOriginalOrderNumber(string $orderNumber, OwnershipContext $ownershipContext): string
    {
        $orderNumbers = $this->fetchOriginalOrderNumbers([$orderNumber], $ownershipContext);
        $originalOrderNumber = array_shift($orderNumbers);

        return $originalOrderNumber ?: $orderNumber;
    }
}
