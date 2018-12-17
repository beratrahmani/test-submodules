<?php declare(strict_types=1);

namespace Shopware\B2B\InStock\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;

class InStockRepository implements GridRepository
{
    const TABLE_NAME = 'b2b_in_stocks';
    const TABLE_ALIAS = 'inStocks';

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
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper
    ) {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
    }

    /**
     * @param InStockEntity $entity
     * @throws CanNotInsertExistingRecordException
     * @return InStockEntity
     */
    public function addInStock(InStockEntity $entity): InStockEntity
    {
        if (!$entity->isNew()) {
            throw new CanNotInsertExistingRecordException('The inStock provided already exists');
        }

        $this->connection->insert(
            self::TABLE_NAME,
            $entity->toDatabaseArray()
        );

        $entity->id = (int) $this->connection->lastInsertId();

        return $entity;
    }

    /**
     * @param InStockEntity $entity
     * @throws CanNotRemoveExistingRecordException
     * @return InStockEntity
     */
    public function removeInStock(InStockEntity $entity): InStockEntity
    {
        if ($entity->isNew()) {
            throw new CanNotRemoveExistingRecordException('The inStock provided does not exist');
        }

        $this->connection->delete(
            self::TABLE_NAME,
            ['id' => $entity->id]
        );

        $entity->id = null;

        return $entity;
    }

    /**
     * @param InStockEntity $entity
     * @throws CanNotUpdateExistingRecordException
     * @return InStockEntity
     */
    public function updateInStock(InStockEntity $entity): InStockEntity
    {
        if ($entity->isNew()) {
            throw new CanNotUpdateExistingRecordException('The inStock provided does not exist');
        }

        $this->connection->update(
            self::TABLE_NAME,
            $entity->toDatabaseArray(),
            ['id' => $entity->id]
        );

        return $entity;
    }

    /**
     * @param int $authId
     * @param InStockSearchStruct $searchStruct
     * @throws NotFoundException
     * @return InStockEntity[]
     */
    public function fetchInStocksByAuthId(int $authId, InStockSearchStruct $searchStruct): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.auth_id = :authId')
            ->setParameter('authId', $authId);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $data = $query->execute()->fetchAll();

        $inStocks = [];
        foreach ($data as $inStock) {
            /** @var InStockEntity $entity */
            $entity = (new InStockEntity())->fromDatabaseArray($inStock);
            $inStocks[$entity->articlesDetailsId] = $entity;
        }

        return $inStocks;
    }

    /**
     * @param int $id
     * @throws NotFoundException
     * @return InStockEntity
     */
    public function fetchOneById(int $id): InStockEntity
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->setParameter('id', $id)
            ->execute();

        $data = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            throw new NotFoundException('inStock for id "' . $id . '"" not found');
        }

        $entity = new InStockEntity();

        $entity->fromDatabaseArray($data);

        return $entity;
    }

    /**
     * @param int $authId
     * @param InStockSearchStruct $searchStruct
     * @return int
     */
    public function fetchTotalCount(int $authId, InStockSearchStruct $searchStruct): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.auth_id = :authId')
            ->setParameter('authId', $authId);

        $this->dbalHelper->applyFilters($searchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @return string
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
            'in_stock',
            'articles_details_id',
            'auth_id',
        ];
    }

    /**
     * @return array
     */
    public function getAdditionalSearchResourceAndFields(): array
    {
        return [];
    }
}
