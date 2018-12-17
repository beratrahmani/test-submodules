<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;

class LineItemReferenceRepository implements GridRepository
{
    const TABLE_NAME = 'b2b_line_item_reference';

    const TABLE_ALIAS = 'lineItem';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var ProductProviderInterface
     */
    private $productProvider;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param ProductProviderInterface $productProvider
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        ProductProviderInterface $productProvider
    ) {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
        $this->productProvider = $productProvider;
    }

    /**
     * @param int $listId
     * @param LineItemReferenceSearchStruct $searchStruct
     * @return LineItemReference[]
     */
    public function fetchList(int $listId, LineItemReferenceSearchStruct $searchStruct): array
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.list_id = :listId')
            ->setParameter('listId', $listId)
            ->orderBy('sort');

        $this->dbalHelper->applyFilters($searchStruct, $queryBuilder);

        $rawItemReferences = $queryBuilder
            ->execute()
            ->fetchAll();

        $lineItemReferences = [];
        foreach ($rawItemReferences as $rawItemReference) {
            $lineItemReference = (new LineItemReference())
                ->fromDatabaseArray($rawItemReference);
            $this->productProvider->setMaxMinAndSteps($lineItemReference);
            $lineItemReferences[] = $lineItemReference;
        }

        return $lineItemReferences;
    }

    /**
     * @param int $listId
     * @param LineItemReferenceSearchStruct $searchStruct
     * @return int
     */
    public function fetchTotalCount(int $listId, LineItemReferenceSearchStruct $searchStruct): int
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.list_id = :listId')
            ->setParameter('listId', $listId);

        $this->dbalHelper->applyFilters($searchStruct, $queryBuilder);

        return (int) $queryBuilder
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param int $listId
     * @return LineItemReference[]
     */
    public function fetchAllForList(int $listId): array
    {
        $lineItemData = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.list_id = :listId')
            ->orderBy(self::TABLE_ALIAS . '.sort')
            ->setParameter('listId', $listId)
            ->execute()
            ->fetchAll();

        $lineItems = [];

        foreach ($lineItemData as $key => $rawLineItem) {
            $lineItem = new LineItemReference();
            $lineItem->fromDatabaseArray($rawLineItem);
            $this->productProvider->setMaxMinAndSteps($lineItem);

            if (array_key_exists($key - 1, $lineItemData)) {
                $lineItem->previousItem = $lineItemData[$key - 1]['id'];
            }

            if (array_key_exists($key + 1, $lineItemData)) {
                $lineItem->nextItem = $lineItemData[$key + 1]['id'];
            }

            $lineItems[] = $lineItem;
        }

        return $lineItems;
    }

    /**
     * @param int $id
     * @throws NotFoundException
     * @return LineItemReference
     */
    public function fetchReferenceById(int $id): LineItemReference
    {
        $rawReferenceData = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->setParameter('id', $id)
            ->execute()
            ->fetch();

        if (!$rawReferenceData) {
            throw new NotFoundException(sprintf('reference with id %d not found', $id));
        }

        $lineItemReference = new LineItemReference();
        $lineItemReference->fromDatabaseArray($rawReferenceData);
        $this->productProvider->setMaxMinAndSteps($lineItemReference);

        return $lineItemReference;
    }

    /**
     * @param LineItemReference $lineItemReferenceOne
     * @param LineItemReference $lineItemReferenceTwo
     */
    public function flipSorting(LineItemReference $lineItemReferenceOne, LineItemReference $lineItemReferenceTwo)
    {
        $this->connection->update(
            self::TABLE_NAME,
            [
                'sort' => $lineItemReferenceTwo->sort,
            ],
            $lineItemReferenceOne->toDatabaseArray()
        );

        $this->connection->update(
            self::TABLE_NAME,
            [
                'sort' => $lineItemReferenceOne->sort,
            ],
            $lineItemReferenceTwo->toDatabaseArray()
        );
    }

    /**
     * @param string $referenceNumber
     * @param int $listId
     * @return bool
     */
    public function hasReference(string $referenceNumber, int $listId): bool
    {
        return (bool) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.reference_number = :referenceNumber')
            ->andWhere(self::TABLE_ALIAS . '.list_id = :listId')
            ->setParameter('referenceNumber', $referenceNumber)
            ->setParameter('listId', $listId)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param string $referenceNumber
     * @param int $listId
     * @return LineItemReference
     */
    public function getReferenceByReferenceNumberAndListId(string $referenceNumber, int $listId): LineItemReference
    {
        $rawReferenceData = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.reference_number = :referenceNumber')
            ->andWhere(self::TABLE_ALIAS . '.list_id = :listId')
            ->setParameter('referenceNumber', $referenceNumber)
            ->setParameter('listId', $listId)
            ->execute()
            ->fetch();

        if (!$rawReferenceData) {
            throw new NotFoundException(sprintf('Reference not found in list with id %d', $listId));
        }

        $lineItemReference = new LineItemReference();
        $lineItemReference->fromDatabaseArray($rawReferenceData);
        $this->productProvider->setMaxMinAndSteps($lineItemReference);

        return $lineItemReference;
    }

    /**
     * @param int $listId
     * @param LineItemReference $lineItemReference
     * @throws CanNotInsertExistingRecordException
     * @return LineItemReference
     */
    public function addReference(int $listId, LineItemReference $lineItemReference): LineItemReference
    {
        if (!$lineItemReference->isNew()) {
            throw new CanNotInsertExistingRecordException('The list item provided already exists');
        }

        $references = $this->fetchAllForList($listId);
        if (!$lineItemReference->sort && $references) {
            $lineItemReference = $this->increaseSortIdentifier($lineItemReference, $listId);
        } elseif (!$lineItemReference->sort) {
            $lineItemReference->sort = 0;
        }

        $this->connection->insert(
            self::TABLE_NAME,
            array_merge(
                $lineItemReference->toDatabaseArray(),
                ['list_id' => $listId]
            )
        );

        $lineItemReference->id = (int) $this->connection
            ->lastInsertId();

        return $lineItemReference;
    }

    /**
     * @param int $listId
     * @param LineItemReference $lineItemReference
     */
    public function updateReference(int $listId, LineItemReference $lineItemReference)
    {
        if (!$lineItemReference->sort) {
            $lineItemReference = $this->increaseSortIdentifier($lineItemReference, $listId);
        }

        $this->connection->update(
            self::TABLE_NAME,
            $lineItemReference->toDatabaseArray(),
            [
                'id' => $lineItemReference->id,
                'list_id' => $listId,
            ]
        );
    }

    /**
     * @internal
     * @param LineItemReference $lineItemReference
     * @param int $listId
     * @return LineItemReference
     */
    protected function increaseSortIdentifier(LineItemReference $lineItemReference, int $listId): LineItemReference
    {
        $maxSortIdentifier = (int) $this->connection->fetchColumn('SELECT MAX(sort) FROM ' . self::TABLE_NAME . ' WHERE list_id = ' . $listId);
        $lineItemReference->sort = $maxSortIdentifier + 1;

        return $lineItemReference;
    }

    /**
     * @param int $id
     */
    public function removeReference(int $id)
    {
        $this->connection->delete(
            self::TABLE_NAME,
            ['id' => $id]
        );
    }

    /**
     * @param LineItemReference[] $references
     * @param int $listId
     */
    public function syncReferences(int $listId, array $references)
    {
        $ids = [];

        foreach ($references as $reference) {
            if ($reference->isNew()) {
                $this->addReference($listId, $reference);
            } else {
                $this->updateReference($listId, $reference);
            }

            $ids[] = $reference->id;
        }

        $this->connection->createQueryBuilder()
            ->delete(self::TABLE_NAME)
            ->where('id NOT IN (:ids) AND list_id = :listId')
            ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY)
            ->setParameter('listId', $listId)
            ->execute();
    }

    /**
     * @param int $id
     */
    public function removeReferenceByListId(int $id)
    {
        $this->connection->delete(
            self::TABLE_NAME,
            ['list_id' => $id]
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
            'reference_number',
            'comment',
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
     * @param LineItemReference $lineItemReference
     */
    public function updatePrices(LineItemReference $lineItemReference)
    {
        $this->connection->update(
            self::TABLE_NAME,
            [
                'amount_net' => $lineItemReference->amountNet,
                'amount' => $lineItemReference->amount,
            ],
            ['id' => $lineItemReference->id]
        );
    }
}
