<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyCalculator;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OfferRepository implements GridRepository
{
    const TABLE_NAME = 'b2b_offer';

    const TABLE_ALIAS = 'Offer';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CurrencyCalculator
     */
    private $currencyCalculator;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var LineItemListRepository
     */
    private $lineItemListRepository;

    /**
     * @var OfferStatusProvider
     */
    private $offerStatusProvider;

    /**
     * @param Connection $connection
     * @param CurrencyCalculator $currencyCalculator
     * @param DbalHelper $dbalHelper
     * @param LineItemListRepository $lineItemListRepository
     * @param OfferStatusProvider $offerStatusProvider
     */
    public function __construct(
        Connection $connection,
        CurrencyCalculator $currencyCalculator,
        DbalHelper $dbalHelper,
        LineItemListRepository $lineItemListRepository,
        OfferStatusProvider $offerStatusProvider
    ) {
        $this->connection = $connection;
        $this->currencyCalculator = $currencyCalculator;
        $this->dbalHelper = $dbalHelper;
        $this->lineItemListRepository = $lineItemListRepository;
        $this->offerStatusProvider = $offerStatusProvider;
    }

    /**
     * @param OwnershipContext $context
     * @param OfferSearchStruct $searchStruct
     * @param CurrencyContext $currencyContext
     * @return OfferEntity[]
     */
    public function fetchList(OwnershipContext $context, OfferSearchStruct $searchStruct, CurrencyContext $currencyContext): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.converted_at is NULL');

        $this->filterByContextOwner($context, $query);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $statement = $query->execute();
        $rawOffers = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $offers = [];

        foreach ($rawOffers as $rawOffer) {
            $offer = (new OfferEntity())->fromDatabaseArray($rawOffer);
            $offers[] = $offer;
        }

        $this->offerStatusProvider->determinateStatusForOffers($offers);
        $this->currencyCalculator->recalculateAmounts($offers, $currencyContext);

        return $offers;
    }

    /**
     * @param OfferSearchStruct $searchStruct
     * @param CurrencyContext $currencyContext
     * @return OfferEntity[]
     */
    public function fetchBackendList(OfferSearchStruct $searchStruct, CurrencyContext $currencyContext): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*', 'COUNT(line_item_reference.id) as line_item_reference_count')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.converted_at is NULL')
            ->leftJoin(self::TABLE_ALIAS, 'b2b_line_item_reference', 'line_item_reference', self::TABLE_ALIAS . '.list_id = line_item_reference.list_id')
            ->groupBy(self::TABLE_ALIAS . '.id');

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        if ($searchStruct->searchStatus) {
            $this->filterBySearchStatus($searchStruct->searchStatus, $query);
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $statement = $query->execute();
        $rawOffers = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $offers = [];
        $offers = array_map(
            function (array $rawOffer) use ($offers) {
                return $offers[] = (new OfferEntity())->fromDatabaseArray($rawOffer);
            },
            $rawOffers
        );

        $this->currencyCalculator->recalculateAmounts($offers, $currencyContext);
        $this->offerStatusProvider->determinateStatusForOffers($offers);

        return $offers;
    }

    /**
     * @internal
     * @param string $status
     * @param QueryBuilder $query
     */
    protected function filterBySearchStatus(string $status, QueryBuilder $query)
    {
        switch ($status) {
            case (OfferEntity::STATE_CONVERTED):
                $query->andWhere(self::TABLE_ALIAS . '.converted_at is NOT NULL');

                break;
            case (OfferEntity::STATE_EXPIRED):
                $query->andWhere(self::TABLE_ALIAS . '.expired_at < NOW()')
                      ->andWhere(self::TABLE_ALIAS . '.converted_at is NULL');

                break;
            case (OfferEntity::STATE_ACCEPTED_OF_BOTH):
                $query->andWhere(self::TABLE_ALIAS . '.accepted_user_at is NOT NULL')
                      ->andWhere(self::TABLE_ALIAS . '.accepted_admin_at is NOT NULL')
                      ->andWhere(self::TABLE_ALIAS . '.declined_user_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.declined_admin_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.converted_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.expired_at > NOW() OR ' . self::TABLE_ALIAS . '.expired_at is NULL');

                break;
            case (OfferEntity::STATE_ACCEPTED_USER):
                $query->andWhere(self::TABLE_ALIAS . '.accepted_user_at is NOT NULL')
                      ->andWhere(self::TABLE_ALIAS . '.converted_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.accepted_admin_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.expired_at > NOW() OR ' . self::TABLE_ALIAS . '.expired_at is NULL');

                break;
            case (OfferEntity::STATE_DECLINED_USER):
                $query->andWhere(self::TABLE_ALIAS . '.declined_user_at is NOT NULL')
                      ->andWhere(self::TABLE_ALIAS . '.converted_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.expired_at > NOW() OR ' . self::TABLE_ALIAS . '.expired_at is NULL');

                break;
            case (OfferEntity::STATE_ACCEPTED_ADMIN):
                $query->andWhere(self::TABLE_ALIAS . '.accepted_admin_at is NOT NULL')
                      ->andWhere(self::TABLE_ALIAS . '.converted_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.accepted_user_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.expired_at > NOW() OR ' . self::TABLE_ALIAS . '.expired_at is NULL');

                break;
            case (OfferEntity::STATE_DECLINED_ADMIN):
                $query->andWhere(self::TABLE_ALIAS . '.declined_admin_at is NOT NULL')
                      ->andWhere(self::TABLE_ALIAS . '.converted_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.expired_at > NOW() OR ' . self::TABLE_ALIAS . '.expired_at is NULL');

                break;
            case (OfferEntity::STATE_OPEN):
                $query->andWhere(self::TABLE_ALIAS . '.created_at is NOT NULL')
                      ->andWhere(self::TABLE_ALIAS . '.declined_admin_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.declined_user_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.accepted_user_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.accepted_admin_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.expired_at > NOW() OR ' . self::TABLE_ALIAS . '.expired_at is NULL')
                      ->andWhere(self::TABLE_ALIAS . '.converted_at is NULL');

                break;
        }
    }

    /**
     * @param OfferSearchStruct $searchStruct
     * @return int
     */
    public function fetchTotalCountForBackend(OfferSearchStruct $searchStruct): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select('Count(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS);

        if ($searchStruct->searchStatus) {
            $this->filterBySearchStatus($searchStruct->searchStatus, $query);
        }

        $this->dbalHelper->applyFilters($searchStruct, $query);

        return (int) $query->execute()->fetchColumn();
    }

    /**
     * @param OwnershipContext $context
     * @param OfferSearchStruct $searchStruct
     * @return int
     */
    public function fetchTotalCount(OwnershipContext $context, OfferSearchStruct $searchStruct): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.converted_at is NULL');

        $this->filterByContextOwner($context, $query);

        $this->dbalHelper->applyFilters($searchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @internal
     *
     * @param OwnershipContext $ownershipContext
     * @param $query
     */
    protected function filterByContextOwner(OwnershipContext $ownershipContext, QueryBuilder $query)
    {
        $query->andWhere(
            self::TABLE_ALIAS . '.auth_id IN (SELECT DISTINCT id FROM b2b_store_front_auth WHERE '
            . self::TABLE_ALIAS . '.auth_id = :authId OR context_owner_id = :authId)'
        )->setParameter('authId', $ownershipContext->contextOwnerId);
    }

    /**
     * @param OfferEntity $offer
     * @throws CanNotUpdateExistingRecordException
     * @return OfferEntity
     */
    public function updateOffer(OfferEntity $offer): OfferEntity
    {
        if ($offer->isNew()) {
            throw new CanNotUpdateExistingRecordException('Offer is not yet created.');
        }

        $this->connection
            ->update(self::TABLE_NAME, $offer->toDatabaseArray(), ['id' => $offer->id]);

        $this->offerStatusProvider->determinateStatusForOffer($offer);

        return $offer;
    }

    /**
     * @param OfferEntity $offer
     * @throws CanNotUpdateExistingRecordException
     * @return OfferEntity
     */
    public function updateOfferDates(OfferEntity $offer): OfferEntity
    {
        if ($offer->isNew()) {
            throw new CanNotUpdateExistingRecordException('Offer is not yet created.');
        }

        $this->connection
            ->update(self::TABLE_NAME, $offer->datesToDatabaseArray(), ['id' => $offer->id]);

        $this->offerStatusProvider->determinateStatusForOffer($offer);

        return $offer;
    }

    /**
     * @param OfferEntity $offer
     * @param OwnershipContext $ownershipContext
     * @throws CanNotRemoveExistingRecordException
     * @return OfferEntity
     */
    public function removeOffer(OfferEntity $offer, OwnershipContext $ownershipContext): OfferEntity
    {
        if ($offer->isNew()) {
            throw new CanNotRemoveExistingRecordException('The provided offer does not exists');
        }

        $queryBuilder = $this->connection->createQueryBuilder()
            ->delete(self::TABLE_NAME)
            ->where('id = :offerId')
            ->andWhere('auth_id IN (SELECT DISTINCT id FROM b2b_store_front_auth WHERE auth_id = :authId OR context_owner_id = :authId)')
            ->setParameter('offerId', $offer->id)
            ->setParameter('authId', $ownershipContext->contextOwnerId);

        $numRows = $queryBuilder->execute();

        if (!$numRows) {
            throw new NotFoundException('The provided offer does not exists');
        }

        $this->lineItemListRepository->removeLineItemListById($offer->listId, $ownershipContext);

        $this->connection->delete(
            'b2b_order_context',
            ['list_id' => $offer->listId]
        );

        $offer->id = null;
        $offer->orderContextId = null;
        $this->offerStatusProvider->determinateStatusForOffer($offer);

        return $offer;
    }

    /**
     * @param OfferEntity $offer
     * @return OfferEntity
     */
    public function removeOfferWithoutContext(OfferEntity $offer): OfferEntity
    {
        if ($offer->isNew()) {
            throw new CanNotRemoveExistingRecordException('The provided offer does not exists');
        }

        $this->connection->delete(
            self::TABLE_NAME,
            ['id' => $offer->id]
        );

        $offer->id = null;
        $this->offerStatusProvider->determinateStatusForOffer($offer);

        return $offer;
    }

    /**
     * @param OfferEntity $offer
     * @throws CanNotInsertExistingRecordException
     * @return OfferEntity
     */
    public function addOffer(OfferEntity $offer): OfferEntity
    {
        if (!$offer->isNew()) {
            throw new CanNotInsertExistingRecordException('The contact provided already exists');
        }

        $this->connection->insert(
            self::TABLE_NAME,
            $offer->toDatabaseArray()
        );

        $offer->id = (int) $this->connection->lastInsertId();

        $this->offerStatusProvider->determinateStatusForOffer($offer);

        return $offer;
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
            'created_at',
            'expired_at',
            'changed_user_at',
            'changed_admin_at',
            'discount_amount',
            'discount_amount_net',
            'email',
            'debtor_email',
            'accepted_user_at',
            'accepted_admin_at',
            'declined_admin_at',
            'declined_user_at',
            'changed_status_at',
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
     * @param int $offerId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @throws NotFoundException
     * @return OfferEntity
     */
    public function fetchOfferById(int $offerId, CurrencyContext $currencyContext, OwnershipContext $ownershipContext): OfferEntity
    {
        $offerQuery = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :offerId')
            ->setParameter('offerId', $offerId);

        $this->filterByContextOwner($ownershipContext, $offerQuery);

        $offerEntityData = $offerQuery->execute()->fetch(\PDO::FETCH_ASSOC);

        if (!$offerEntityData) {
            throw new NotFoundException(sprintf('Offer not found for %s', $offerId));
        }

        $offerEntity = (new OfferEntity())->fromDatabaseArray($offerEntityData);

        $this->currencyCalculator->recalculateAmount($offerEntity, $currencyContext);
        $this->offerStatusProvider->determinateStatusForOffer($offerEntity);

        return $offerEntity;
    }

    /**
     * @param int $orderContextId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $context
     * @return OfferEntity
     */
    public function fetchOfferByOrderContextId(int $orderContextId, CurrencyContext $currencyContext, OwnershipContext $context): OfferEntity
    {
        $offerQuery = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.order_context_id = ' . $orderContextId);

        $this->filterByContextOwner($context, $offerQuery);

        $offerEntityData = $offerQuery->execute()->fetch(\PDO::FETCH_ASSOC);

        if (!$offerEntityData) {
            throw new NotFoundException(sprintf('Offer not found for %s', $orderContextId));
        }

        $offerEntity = (new OfferEntity())->fromDatabaseArray($offerEntityData);

        $this->currencyCalculator->recalculateAmount($offerEntity, $currencyContext);
        $this->offerStatusProvider->determinateStatusForOffer($offerEntity);

        return $offerEntity;
    }

    /**
     * @param OfferEntity $offerEntity
     */
    public function updateOfferPrices(OfferEntity $offerEntity)
    {
        $this->connection->update(
            self::TABLE_NAME,
            [
                'discount_amount' => $offerEntity->discountAmount,
                'discount_amount_net' => $offerEntity->discountAmountNet,
                'currency_factor' => $offerEntity->currencyFactor,
            ],
            ['id' => $offerEntity->id]
        );
    }

    /**
     * @param int $offerId
     * @return int
     */
    public function fetchAuthIdFromOfferById(int $offerId): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.auth_id')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->setParameter('id', $offerId);

        $authId = $query->execute()->fetchColumn();

        if (!$authId) {
            throw new NotFoundException(sprintf('Offer not found for %s', $offerId));
        }

        return (int) $authId;
    }
}
