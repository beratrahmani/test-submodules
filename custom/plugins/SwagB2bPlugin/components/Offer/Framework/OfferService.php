<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use InvalidArgumentException;
use Shopware\B2B\AuditLog\Framework\AuditLogValueBasicEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueOrderCommentEntity;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\Debtor\Framework\DebtorEntity;
use Shopware\B2B\Debtor\Framework\DebtorRepository;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\Order\Framework\OrderRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OfferService
{
    const ORDER_STATUS = 0;

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var OfferShopWriterServiceInterface
     */
    private $offerShopWriterService;

    /**
     * @var TaxProviderInterface
     */
    private $taxProvider;

    /**
     * @var DebtorRepository
     */
    private $debtorRepository;

    /**
     * @var OfferAuditLogService
     */
    private $auditLog;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

    /**
     * @param OfferRepository $offerRepository
     * @param OfferShopWriterServiceInterface $offerShopWriterService
     * @param TaxProviderInterface $taxProvider
     * @param DebtorRepository $debtorRepository
     * @param OfferAuditLogService $auditLog
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderContextRepository $orderContextRepository
     */
    public function __construct(
        OfferRepository $offerRepository,
        OfferShopWriterServiceInterface $offerShopWriterService,
        TaxProviderInterface $taxProvider,
        DebtorRepository $debtorRepository,
        OfferAuditLogService $auditLog,
        OrderRepositoryInterface $orderRepository,
        OrderContextRepository $orderContextRepository
    ) {
        $this->offerRepository = $offerRepository;
        $this->offerShopWriterService = $offerShopWriterService;
        $this->auditLog = $auditLog;
        $this->taxProvider = $taxProvider;
        $this->debtorRepository = $debtorRepository;
        $this->orderRepository = $orderRepository;
        $this->orderContextRepository = $orderContextRepository;
    }

    /**
     * @param CurrencyContext $currencyContext
     * @param OrderContext $orderContext
     * @param LineItemList $lineItemList
     * @param Identity $identity
     * @return OfferEntity
     */
    public function createOfferThroughCheckoutSource(
        Identity $identity,
        CurrencyContext $currencyContext,
        OrderContext $orderContext,
        LineItemList $lineItemList
    ): OfferEntity {
        $offer = new OfferEntity();
        $offer->updateDates(['createdAt']);

        $offer->discountAmount = $lineItemList->amount;
        $offer->discountAmountNet = $lineItemList->amountNet;

        $offer->authId = $identity->getOwnershipContext()->authId;
        $offer->email = $identity->getEntity()->email;
        $offer->debtorEmail = $identity->getOwnershipContext()->shopOwnerEmail;
        $offer->orderContextId = $orderContext->id;
        $offer->currencyFactor = $currencyContext->currentCurrencyFactor;
        $offer->listId = $lineItemList->id;

        $this->offerRepository->addOffer($offer);

        $this->offerRepository->updateOfferDates($offer);

        return $offer;
    }

    /**
     * @param int $offerId
     * @param CurrencyContext $currencyContext
     * @param Identity $identity
     * @throws InvalidArgumentException
     */
    public function sendOfferToAdmin(int $offerId, CurrencyContext $currencyContext, Identity $identity)
    {
        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());

        if (!$offer->discountAmount) {
            throw new InvalidArgumentException('The offer is not valid. The offer amount is equal zero.');
        }

        $oldStatus = $offer->status;
        $offer->id = $offerId;
        $offer->updateDates(['acceptedByUserAt']);
        $offer->removeDates(['declinedByAdminAt', 'declinedByUserAt']);

        $offer = $this->offerRepository->updateOfferDates($offer);
        $newStatus = $offer->status;

        $this->createOfferStatusChangeLogEntry($offer->orderContextId, $identity, $newStatus, $oldStatus, false);
    }

    /**
     * @param int $offerId
     * @param CurrencyContext $currencyContext
     * @param Identity $identity
     * @return OfferEntity
     */
    public function declineOfferByUser(int $offerId, CurrencyContext $currencyContext, Identity $identity): OfferEntity
    {
        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());

        $oldStatus = $offer->status;
        $offer->updateDates(['declinedByUserAt']);
        $offer->removeDates(['acceptedByAdminAt', 'declinedByAdminAt', 'acceptedByUserAt']);

        $this->offerRepository->updateOfferDates($offer);
        $newStatus = $offer->status;

        $this->createOfferStatusChangeLogEntry($offer->orderContextId, $identity, $newStatus, $oldStatus, false);

        return $offer;
    }

    /**
     * @param int $offerId
     * @param CurrencyContext $currencyContext
     * @param Identity $identity
     * @return OfferEntity
     */
    public function declineOffer(int $offerId, CurrencyContext $currencyContext, Identity $identity): OfferEntity
    {
        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());
        $oldStatus = $offer->status;

        $offer->updateDates(['declinedByAdminAt']);
        $offer->removeDates(['acceptedByUserAt', 'acceptedByAdminAt']);

        $offer = $this->offerRepository->updateOfferDates($offer);
        $newStatus = $offer->status;

        $this->createOfferStatusChangeLogEntry($offer->orderContextId, $identity, $newStatus, $oldStatus, true);

        return $offer;
    }

    /**
     * @param int $offerId
     * @param CurrencyContext $currencyContext
     * @param Identity $identity
     * @return OfferEntity
     */
    public function acceptOffer(int $offerId, CurrencyContext $currencyContext, Identity $identity): OfferEntity
    {
        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());

        if ($offer->discountAmountNet < 0) {
            throw new DiscountGreaterThanAmountException();
        }

        $oldStatus = $offer->status;

        $offer->updateDates(['acceptedByAdminAt']);
        $offer->removeDates(['declinedByAdminAt']);

        $this->offerRepository->updateOfferDates($offer);
        $newStatus = $offer->status;

        $this->createOfferStatusChangeLogEntry($offer->orderContextId, $identity, $newStatus, $oldStatus, true);

        return $offer;
    }

    /**
     * @param int $offerId
     * @param LineItemList $lineItemList
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return OfferEntity
     */
    public function updateOfferPrices(
        int $offerId,
        LineItemList $lineItemList,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): OfferEntity {
        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $ownershipContext);

        $this->updateOffer($offer, $lineItemList, $currencyContext, $ownershipContext);

        $this->offerRepository
            ->updateOfferPrices($offer);

        return $offer;
    }

    /**
     * @param int $offerId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $context
     */
    public function convertOffer(int $offerId, CurrencyContext $currencyContext, OwnershipContext $context)
    {
        $offer = $this->offerRepository
            ->fetchOfferById($offerId, $currencyContext, $context);

        $orderContext = $this->orderContextRepository->fetchOneOrderContextById($offer->orderContextId, $context);

        $this->offerShopWriterService
            ->sendToCheckout($orderContext);
    }

    /**
     * @param int $orderContextId
     * @param Identity $identity
     */
    public function createOfferCreatedStatusChangeLogEntry(int $orderContextId, Identity $identity)
    {
        $auditLogValue = $this
            ->createOfferAuditLogValue(
                OfferEntity::STATE_CONVERTED,
                OfferEntity::STATE_ACCEPTED_OF_BOTH
            );

        $this->auditLog->createOfferAuditLog(
            $orderContextId,
            $auditLogValue,
            $identity,
            false
        );
    }

    /**
     * @param int $orderContextId
     * @param Identity $identity
     * @param float $newDiscount
     * @param float $oldDiscount
     * @param CurrencyContext $currencyContext
     * @param bool $isBackend
     */
    public function createOfferDiscountLogEntry(
        int $orderContextId,
        Identity $identity,
        float $newDiscount,
        float $oldDiscount,
        CurrencyContext $currencyContext,
        bool $isBackend
    ) {
        $auditLogValue = $this
            ->createDiscountAuditLogValue(
                (string) $newDiscount,
                (string) $oldDiscount,
                $currencyContext
            );

        $this->auditLog->createOfferAuditLog(
            $orderContextId,
            $auditLogValue,
            $identity,
            $isBackend
        );
    }

    /**
     * @param int $orderContextId
     * @param Identity $identity
     * @param string $newStatus
     * @param string $oldStatus
     * @param bool $isBackend
     */
    public function createOfferStatusChangeLogEntry(
        int $orderContextId,
        Identity $identity,
        string $newStatus,
        string $oldStatus,
        bool $isBackend
    ) {
        $auditLogValue = $this
            ->createOfferAuditLogValue(
                $newStatus,
                $oldStatus
            );

        $this->auditLog->createOfferAuditLog(
            $orderContextId,
            $auditLogValue,
            $identity,
            $isBackend
        );
    }

    /**
     * @param int $orderContextId
     * @param Identity $identity
     * @param \DateTime $newValue
     * @param bool $isBackend
     */
    public function createOfferExpirationChangeLogEntry(
        int $orderContextId,
        Identity $identity,
        \DateTime $newValue,
        bool $isBackend
    ) {
        $auditLogValue = new AuditLogExpirationDate();
        $auditLogValue->newValue = $newValue;

        $this->auditLog->createOfferAuditLog(
            $orderContextId,
            $auditLogValue,
            $identity,
            $isBackend
        );
    }

    /**
     * @internal
     * @param string $newValue
     * @param string $oldValue
     * @return AuditLogValueBasicEntity
     */
    protected function createOfferAuditLogValue(string $newValue, string $oldValue): AuditLogValueBasicEntity
    {
        $auditLogValue = new AuditLogValueOfferDiffEntity();
        $auditLogValue->newValue = $newValue;
        $auditLogValue->oldValue = $oldValue;

        return $auditLogValue;
    }

    /**
     * @internal
     * @param string $newValue
     * @param string $oldValue
     * @param CurrencyContext $currencyContext
     * @return AuditLogValueBasicEntity
     */
    protected function createDiscountAuditLogValue(string $newValue, string $oldValue, CurrencyContext $currencyContext): AuditLogValueBasicEntity
    {
        $auditLogValue = new AuditLogDiscountEntity();
        $auditLogValue->newValue = $newValue;
        $auditLogValue->oldValue = $oldValue;
        $auditLogValue->setCurrencyFactor($currencyContext->currentCurrencyFactor);

        return $auditLogValue;
    }

    public function stopOffer()
    {
        $this->offerShopWriterService->stopCheckout();
    }

    /**
     * @param int $offerId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return DebtorEntity
     */
    public function fetchDebtorByOfferId(
        int $offerId,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): DebtorEntity {
        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $ownershipContext);

        return $this->debtorRepository->fetchOneByEmail($offer->debtorEmail);
    }

    /**
     * @param OfferEntity $offerEntity
     * @param LineItemList $list
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     */
    public function updateOffer(
        OfferEntity $offerEntity,
        LineItemList $list,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ) {
        $totalAmount = 0;
        $totalAmountNet = 0;

        /** @var OfferLineItemReferenceEntity $reference */
        foreach ($list->references as $reference) {
            $baseGrossPrice = $reference->discountAmount / $reference->discountCurrencyFactor;
            $totalAmount += $baseGrossPrice * $currencyContext->currentCurrencyFactor * $reference->quantity;

            $baseNetPrice = $reference->discountAmountNet / $reference->discountCurrencyFactor;
            $totalAmountNet += $baseNetPrice * $currencyContext->currentCurrencyFactor * $reference->quantity;
        }

        $offerEntity->discountAmount =
            $totalAmount - $offerEntity->discountValueNet * $this->taxProvider->getDiscountTax($list->id, $ownershipContext);
        $offerEntity->discountAmountNet =
            $totalAmountNet - $offerEntity->discountValueNet;
        $offerEntity->currencyFactor =
            $currencyContext->currentCurrencyFactor;
    }

    /**
     * @param string $expiredDate
     * @param OfferEntity $offer
     * @param Identity $identity
     * @return OfferEntity
     */
    public function updateExpiredDate(string $expiredDate, OfferEntity $offer, Identity $identity): OfferEntity
    {
        if (!$expiredDate) {
            $offer->removeDates(['expiredAt']);
        } else {
            $offer->setDates(['expiredAt' => new \DateTime($expiredDate)]);

            $this->createOfferExpirationChangeLogEntry($offer->orderContextId, $identity, $offer->expiredAt, true);
        }

        return $this->offerRepository->updateOfferDates($offer);
    }

    /**
     * @param string $comment
     * @param OrderContext $orderContext
     * @param Identity $identity
     * @param bool $isBackend
     */
    public function saveComment(string $comment, OrderContext $orderContext, Identity $identity, bool $isBackend)
    {
        $comment = nl2br($comment);

        if ($comment === $orderContext->comment || (!$isBackend && !$comment)) {
            return;
        }

        $this->orderRepository->setOrderCommentByOrderContextId($orderContext->id, $comment);

        $auditLogValue = new AuditLogValueOrderCommentEntity();
        $auditLogValue->oldValue = $orderContext->comment;
        $auditLogValue->newValue = $comment;


        $this->auditLog->createOfferAuditLog(
            $orderContext->id,
            $auditLogValue,
            $identity,
            $isBackend
        );
    }
}
