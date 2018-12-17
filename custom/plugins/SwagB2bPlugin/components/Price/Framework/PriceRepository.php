<?php declare(strict_types=1);

namespace Shopware\B2B\Price\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;

class PriceRepository implements GridRepository
{
    const TABLE_NAME = 'b2b_prices';
    const TABLE_ALIAS = 'price';

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
     * @param int $debtorId
     * @param string $orderNumber
     * @param int $quantity
     * @throws NotFoundException
     * @return CrudEntity
     */
    public function fetchPriceByDebtorIdAndOrderNumberAndQuantity(int $debtorId, string $orderNumber, int $quantity): CrudEntity
    {
        $priceData = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*, details.ordernumber')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.debtor_id = :id')
            ->innerJoin(self::TABLE_ALIAS, 's_articles_details', 'details', 'details.id = ' . self::TABLE_ALIAS . '.articles_details_id')
            ->andWhere('details.ordernumber = :number')
            ->andWhere(self::TABLE_ALIAS . '.from <= :quantity')
            ->andWhere(self::TABLE_ALIAS . '.to >= :quantity or IFNULL(' . self::TABLE_ALIAS . '.to, 0) = 0')
            ->orderBy(self::TABLE_ALIAS . '.from', 'ASC')
            ->setMaxResults(1)
            ->setParameter('quantity', $quantity)
            ->setParameter('id', $debtorId)
            ->setParameter('number', $orderNumber)
            ->execute()->fetch(\PDO::FETCH_ASSOC);

        if (empty($priceData)) {
            throw new NotFoundException('Unable to locate price');
        }

        $price = new PriceEntity();

        return $price->fromDatabaseArray($priceData);
    }

    /**
     * @param int $debtorId
     * @param array $orderNumbers
     * @return PriceEntity[]
     */
    public function fetchPricesByDebtorIdAndOrderNumber(int $debtorId, array $orderNumbers): array
    {
        $pricesData = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*, details.ordernumber')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_articles_details', 'details', 'details.id = ' . self::TABLE_ALIAS . '.articles_details_id')
            ->where(self::TABLE_ALIAS . '.debtor_id = :id')
            ->andWhere('details.ordernumber IN (:orderNumbers)')
            ->setParameter('id', $debtorId)
            ->setParameter('orderNumbers', $orderNumbers, Connection::PARAM_STR_ARRAY)
            ->execute()->fetchAll();

        $prices = [];
        foreach ($pricesData as $price) {
            $prices[] = (new PriceEntity())->fromDatabaseArray($price);
        }

        return $prices;
    }

    /**
     * @param PriceEntity $priceEntity
     * @throws CanNotInsertExistingRecordException
     * @return PriceEntity
     */
    public function addPrice(PriceEntity $priceEntity): PriceEntity
    {
        if (!$priceEntity->isNew()) {
            throw new CanNotInsertExistingRecordException('The price provided already exists');
        }

        $this->connection->insert(
            self::TABLE_NAME,
            $priceEntity->toDatabaseArray()
        );

        $priceEntity->id = (int) $this->connection->lastInsertId();

        return $priceEntity;
    }

    /**
     * @param PriceEntity $priceEntity
     * @throws CanNotRemoveExistingRecordException
     * @return PriceEntity
     */
    public function removePrice(PriceEntity $priceEntity): PriceEntity
    {
        if ($priceEntity->isNew()) {
            throw new CanNotRemoveExistingRecordException('The price provided does not exist');
        }

        $this->connection->delete(
            self::TABLE_NAME,
            ['id' => $priceEntity->id]
        );

        $priceEntity->id = null;

        return $priceEntity;
    }

    /**
     * @param PriceEntity $priceEntity
     * @throws CanNotUpdateExistingRecordException
     * @return PriceEntity
     */
    public function updatePrice(PriceEntity $priceEntity): PriceEntity
    {
        if ($priceEntity->isNew()) {
            throw new CanNotUpdateExistingRecordException('The price provided does not exist');
        }

        $this->connection->update(
            self::TABLE_NAME,
            [
                '`debtor_id`' => $priceEntity->debtorId,
                '`from`' => $priceEntity->from,
                '`price`' => $priceEntity->price,
                '`to`' => $priceEntity->to,
                '`articles_details_id`' => $priceEntity->articlesDetailsId,
            ],
            [
                'id' => $priceEntity->id,
            ]
        );

        return $priceEntity;
    }

    /**
     * @param int $id
     * @param PriceSearchStruct $searchStruct
     * @return array
     */
    public function fetchPricesByDebtorId(int $id, PriceSearchStruct $searchStruct): array
    {
        $query = $this->connection->createQueryBuilder()
          ->select(self::TABLE_ALIAS . '.*, details.ordernumber')
          ->from(self::TABLE_NAME, self::TABLE_ALIAS)
          ->where(self::TABLE_ALIAS . '.debtor_id = :id')
          ->innerJoin(self::TABLE_ALIAS, 's_articles_details', 'details', 'details.id = ' . self::TABLE_ALIAS . '.articles_details_id')
          ->setParameter('id', $id);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $pricesData = $query->execute()->fetchAll();

        $prices= [];
        foreach ($pricesData as $price) {
            $prices[] = (new PriceEntity())->fromDatabaseArray($price);
        }

        return $prices;
    }

    /**
     * @param int $id
     * @param int $debtorId
     * @return PriceEntity
     */
    public function fetchOneById(int $id, int $debtorId): PriceEntity
    {
        $statement = $this->connection->createQueryBuilder()
          ->select('*')
          ->from(self::TABLE_NAME, self::TABLE_ALIAS)
          ->where(self::TABLE_ALIAS . '.id = :id')
          ->andWhere('debtor_id = :debtorId')
          ->setParameter('id', $id)
          ->setParameter('debtorId', $debtorId)
          ->execute();

        $priceData = $statement->fetch(\PDO::FETCH_ASSOC);

        $price = new PriceEntity();

        $price->fromDatabaseArray($priceData);

        return $price;
    }

    /**
     * @param int $debtorId
     * @param PriceSearchStruct $searchStruct
     * @return int
     */
    public function fetchTotalCount(int $debtorId, PriceSearchStruct $searchStruct): int
    {
        $query = $this->connection->createQueryBuilder()
                    ->select('COUNT(*)')
                    ->from(self::TABLE_NAME, self::TABLE_ALIAS)
                    ->where(self::TABLE_ALIAS . '.debtor_id = :debtorId')
                    ->setParameter('debtorId', $debtorId);

        $this->dbalHelper->applyFilters($searchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @param PriceEntity $priceEntity
     * @return bool
     */
    public function checkForUniquePriceToRange(PriceEntity $priceEntity): bool
    {
        $isUnique = (bool) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where('`from` >=' . $priceEntity->to . ' AND ' . '`to` <=' . $priceEntity->to)
            ->andWhere('debtor_id =' . $priceEntity->debtorId)
            ->andWhere('articles_details_id', $priceEntity->articlesDetailsId)
            ->execute();

        return $isUnique;
    }

    /**
     * @param PriceEntity $priceEntity
     * @return bool
     */
    public function checkForUniquePriceFromRange(PriceEntity $priceEntity): bool
    {
        $isUnique = (bool) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where('`from` >=' . $priceEntity->from . ' AND ' . '`to` <=' . $priceEntity->from)
            ->andWhere('debtor_id =' . $priceEntity->debtorId)
            ->andWhere('articles_details_id', $priceEntity->articlesDetailsId)
            ->execute();

        return $isUnique;
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
            'price',
            'articles_details_id',
            'debtor_id',
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
