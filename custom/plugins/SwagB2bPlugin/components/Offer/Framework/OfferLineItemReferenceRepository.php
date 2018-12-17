<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\LineItemList\Bridge\ProductProvider;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceRepository;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceSearchStruct;
use Shopware\B2B\ProductName\Framework\ProductNameService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OfferLineItemReferenceRepository implements GridRepository
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
     * @var LineItemReferenceRepository
     */
    private $lineItemReferenceRepository;

    /**
     * @var ProductNameService
     */
    private $productNameService;

    /**
     * @var ProductProvider
     */
    private $productProvider;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param LineItemReferenceRepository $lineItemReferenceRepository
     * @param ProductNameService $productNameService
     * @param ProductProvider $productProvider
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        LineItemReferenceRepository $lineItemReferenceRepository,
        ProductNameService $productNameService,
        ProductProvider $productProvider
    ) {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
        $this->lineItemReferenceRepository = $lineItemReferenceRepository;
        $this->productNameService = $productNameService;
        $this->productProvider = $productProvider;
    }

    /**
     * @param int $listId
     * @param OwnershipContext $ownershipContext
     * @return OfferLineItemReferenceEntity[]
     */
    public function fetchAllForList(int $listId, OwnershipContext $ownershipContext): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.list_id = :listId')
            ->orderBy(self::TABLE_ALIAS . '.id')
            ->setParameter('listId', $listId);

        $this->filterByContextOwner($query, $ownershipContext);

        $lineItemData = $query->execute()->fetchAll();

        $lineItems = [];

        foreach ($lineItemData as $rawLineItem) {
            $lineItem = new OfferLineItemReferenceEntity();
            $lineItem->fromDatabaseArray($rawLineItem);
            $this->productNameService->translateProductName($lineItem);
            $this->productProvider->setMaxMinAndSteps($lineItem);

            $lineItems[] = $lineItem;
        }

        return $lineItems;
    }

    /**
     * @param int $listId
     * @param LineItemReferenceSearchStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     * @return OfferLineItemReferenceEntity[]
     */
    public function fetchList(int $listId, LineItemReferenceSearchStruct $searchStruct, OwnershipContext $ownershipContext): array
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.list_id = :listId')
            ->setParameter('listId', $listId);

        $this->filterByContextOwner($queryBuilder, $ownershipContext);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $queryBuilder);

        $rawItemReferences = $queryBuilder
            ->execute()
            ->fetchAll();

        $offerLineItemReferences = [];
        foreach ($rawItemReferences as $rawItemReference) {
            $offerLineItemReference = (new OfferLineItemReferenceEntity())
                ->fromDatabaseArray($rawItemReference);

            $this->productNameService->translateProductName($offerLineItemReference);
            $this->productProvider->setMaxMinAndSteps($offerLineItemReference);
            $offerLineItemReferences[] = $offerLineItemReference;
        }

        return $offerLineItemReferences;
    }

    /**
     * @param int $id
     * @param OwnershipContext $ownershipContext
     * @throws NotFoundException
     * @return OfferLineItemReferenceEntity
     */
    public function fetchReferenceById(int $id, OwnershipContext $ownershipContext): OfferLineItemReferenceEntity
    {
        $rawReferenceDataQuery = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->setParameter('id', $id);

        $this->filterByContextOwner($rawReferenceDataQuery, $ownershipContext);

        $rawReferenceData = $rawReferenceDataQuery->execute()->fetch();

        if (!$rawReferenceData) {
            throw new NotFoundException(sprintf('reference with id %d not found', $id));
        }

        $offerLineItemReference = new OfferLineItemReferenceEntity();
        $offerLineItemReference->fromDatabaseArray($rawReferenceData);
        $this->productNameService->translateProductName($offerLineItemReference);
        $this->productProvider->setMaxMinAndSteps($offerLineItemReference);

        return $offerLineItemReference;
    }

    /**
     * @param int $listId
     * @param LineItemReferenceSearchStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     * @return int
     */
    public function fetchTotalCount(int $listId, LineItemReferenceSearchStruct $searchStruct, OwnershipContext $ownershipContext): int
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.list_id = :listId')
            ->setParameter('listId', $listId);

        $this->filterByContextOwner($queryBuilder, $ownershipContext);
        $this->dbalHelper->applyFilters($searchStruct, $queryBuilder);

        return (int) $queryBuilder
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param string $referenceNumber
     * @param int $listId
     * @return bool
     */
    public function hasReference(string $referenceNumber, int $listId): bool
    {
        return $this->lineItemReferenceRepository->hasReference($referenceNumber, $listId);
    }

    /**
     * @param string $referenceNumber
     * @param int $listId
     * @param int $quantity
     * @param OwnershipContext $ownershipContext
     * @throws NotFoundException
     * @return OfferLineItemReferenceEntity
     */
    public function fetchReferenceByReferenceNumberAndListIdAndQuantity(
        string $referenceNumber,
        int $listId,
        int $quantity,
        OwnershipContext $ownershipContext
    ): OfferLineItemReferenceEntity {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.reference_number = :referenceNumber')
            ->andWhere(self::TABLE_ALIAS . '.list_id = :listId')
            ->andWhere(self::TABLE_ALIAS . '.quantity = :quantity')
            ->setParameter('referenceNumber', $referenceNumber)
            ->setParameter('listId', $listId)
            ->setParameter('quantity', $quantity);

        $this->filterByContextOwner($queryBuilder, $ownershipContext);

        $rawReferenceData = $queryBuilder->execute()->fetch();

        if (!$rawReferenceData) {
            throw new NotFoundException(
                "Reference for list with id {$listId} and refrence number {$referenceNumber} not found"
            );
        }

        $lineItemReference = new OfferLineItemReferenceEntity();
        $lineItemReference->fromDatabaseArray($rawReferenceData);
        $this->productNameService->translateProductName($lineItemReference);
        $this->productProvider->setMaxMinAndSteps($lineItemReference);

        return $lineItemReference;
    }

    /**
     * @param string $referenceNumber
     * @param int $listId
     * @param OwnershipContext $ownershipContext
     * @throws NotFoundException
     * @return OfferLineItemReferenceEntity
     */
    public function fetchReferenceByReferenceNumberAndListId(
        string $referenceNumber,
        int $listId,
        OwnershipContext $ownershipContext
    ): OfferLineItemReferenceEntity {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.reference_number = :referenceNumber')
            ->andWhere(self::TABLE_ALIAS . '.list_id = :listId')
            ->setParameter('referenceNumber', $referenceNumber)
            ->setParameter('listId', $listId);

        $this->filterByContextOwner($queryBuilder, $ownershipContext);

        $rawReferenceData = $queryBuilder->execute()->fetch();

        if (!$rawReferenceData) {
            throw new NotFoundException(
                "Reference for list with id {$listId} and refrence number {$referenceNumber} not found"
            );
        }

        $lineItemReference = new OfferLineItemReferenceEntity();
        $lineItemReference->fromDatabaseArray($rawReferenceData);
        $this->productNameService->translateProductName($lineItemReference);
        $this->productProvider->setMaxMinAndSteps($lineItemReference);

        return $lineItemReference;
    }

    /**
     * @param int $listId
     * @param LineItemReference|OfferLineItemReferenceEntity $reference
     * @return OfferLineItemReferenceEntity
     */
    public function addReference(int $listId, OfferLineItemReferenceEntity $reference): OfferLineItemReferenceEntity
    {
        $lineItem =  $this->lineItemReferenceRepository->addReference($listId, $reference);
        $this->productNameService->translateProductName($lineItem);
        $this->productProvider->setMaxMinAndSteps($lineItem);

        return $lineItem;
    }

    /**
     * @param int $id
     * @param OwnershipContext $ownershipContext
     * @return OfferLineItemReferenceEntity
     */
    public function setDefaultPricesForDiscountForLineItemReferenceId(int $id, OwnershipContext $ownershipContext): OfferLineItemReferenceEntity
    {
        $lineItemReference = $this->fetchReferenceById($id, $ownershipContext);

        $this->connection->update(
            self::TABLE_NAME,
            [
                'discount_amount_net' => $lineItemReference->amountNet,
                'discount_amount' => $lineItemReference->amount,
            ],
            ['id' => $lineItemReference->id]
        );

        $lineItemReference->discountAmount = $lineItemReference->amount;
        $lineItemReference->discountAmountNet = $lineItemReference->amountNet;

        return $lineItemReference;
    }

    /**
     * @param int $listId
     * @param LineItemReference $lineItemReference
     */
    public function updateReference(int $listId, LineItemReference $lineItemReference)
    {
        $this->lineItemReferenceRepository->updateReference($listId, $lineItemReference);
        $this->productNameService->translateProductName($lineItemReference);
        $this->productProvider->setMaxMinAndSteps($lineItemReference);
    }

    /**
     * @param int $id
     */
    public function removeReference(int $id)
    {
        $this->lineItemReferenceRepository->removeReference($id);
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
     * @param QueryBuilder $builder
     * @param OwnershipContext $ownershipContext
     */
    protected function filterByContextOwner(QueryBuilder $builder, OwnershipContext $ownershipContext)
    {
        $builder->innerJoin(self::TABLE_ALIAS, 'b2b_line_item_list', 'lineItemList', self::TABLE_ALIAS . '.list_id = lineItemList.id')
            ->andWhere('lineItemList.context_owner_id = :ownerId')
            ->setParameter('ownerId', $ownershipContext->contextOwnerId);
    }
}
