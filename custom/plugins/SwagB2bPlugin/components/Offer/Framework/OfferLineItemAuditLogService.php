<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\AuditLog\Framework\AuditLogValueBasicEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueLineItemAddEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueLineItemQuantityEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueLineItemRemoveEntity;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\Order\Framework\OrderAuditLogService;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\ProductName\Framework\ProductNameService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\NotAuthenticatedException;

class OfferLineItemAuditLogService
{
    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OrderAuditLogService
     */
    private $orderClearanceAuditLogService;

    /**
     * @var ProductNameService
     */
    private $productNameService;

    /**
     * @param OrderContextRepository $orderContextRepository
     * @param AuthenticationService $authenticationService
     * @param OrderAuditLogService $orderClearanceAuditLogService
     * @param ProductNameService $productNameService
     */
    public function __construct(
        OrderContextRepository $orderContextRepository,
        AuthenticationService $authenticationService,
        OrderAuditLogService $orderClearanceAuditLogService,
        ProductNameService $productNameService
    ) {
        $this->orderContextRepository = $orderContextRepository;
        $this->authenticationService = $authenticationService;
        $this->orderClearanceAuditLogService = $orderClearanceAuditLogService;
        $this->productNameService = $productNameService;
    }

    /**
     * @param int $listId
     * @param LineItemReference $item
     */
    public function createDeleteLineItem(int $listId, LineItemReference $item)
    {
        $productName = $this->getProductName($item);

        $auditLogValue = new AuditLogValueLineItemRemoveEntity();
        $auditLogValue->oldValue = $item->id;
        $auditLogValue->orderNumber = $item->referenceNumber;
        $auditLogValue->productName = $productName;

        $this->createAuditLogEntry($listId, $auditLogValue);
    }

    /**
     * @param int $listId
     * @param OfferLineItemReferenceEntity $item
     */
    public function createAddLineItem(int $listId, OfferLineItemReferenceEntity $item)
    {
        $productName = $this->getProductName($item);

        $auditLogValue = new AuditLogValueLineItemAddEntity();
        $auditLogValue->newValue = (int) $item->quantity;
        $auditLogValue->oldValue = $item->comment;
        $auditLogValue->orderNumber = $item->referenceNumber;
        $auditLogValue->productName = $productName;

        $this->createAuditLogEntry($listId, $auditLogValue);
    }

    /**
     * @param int $listId
     * @param OfferLineItemReferenceEntity $item
     * @param float $price
     * @param CurrencyContext $currencyContext
     */
    public function createLineItemPriceChange(
        int $listId,
        OfferLineItemReferenceEntity $item,
        float $price,
        CurrencyContext $currencyContext
    ) {
        $productName = $this->getProductName($item);

        $auditLogValue = new AuditLogValueLineItemPriceEntity();
        $auditLogValue->oldValue = round($item->discountAmountNet, 2);
        $auditLogValue->newValue = $price;
        $auditLogValue->orderNumber = $item->referenceNumber;
        $auditLogValue->productName = $productName;
        $auditLogValue->setCurrencyFactor($currencyContext->currentCurrencyFactor);

        $this->createAuditLogEntry($listId, $auditLogValue);
    }

    /**
     * @param int $listId
     * @param OfferLineItemReferenceEntity $item
     * @param int $newQuantity
     */
    public function createLineItemChange(int $listId, OfferLineItemReferenceEntity $item, int $newQuantity)
    {
        $productName = $this->getProductName($item);

        $auditLogValue = new AuditLogValueLineItemQuantityEntity();
        $auditLogValue->oldValue = (int) $item->quantity;
        $auditLogValue->newValue = $newQuantity;
        $auditLogValue->orderNumber = $item->referenceNumber;
        $auditLogValue->productName = $productName;

        $this->createAuditLogEntry($listId, $auditLogValue);
    }

    /**
     * @internal
     * @param OfferLineItemReferenceEntity $item
     * @return string
     */
    protected function getProductName(OfferLineItemReferenceEntity $item): string
    {
        $this->productNameService->translateProductName($item);

        return $item->name;
    }

    /**
     * @internal
     * @param int $listId
     * @param $auditLogValue
     */
    protected function createAuditLogEntry(int $listId, AuditLogValueBasicEntity $auditLogValue)
    {
        $references = $this->orderContextRepository
            ->fetchAuditLogReferencesByListId($listId);

        try {
            $identity = $this->authenticationService
                ->getIdentity();
        } catch (NotAuthenticatedException $e) {
            $this->orderClearanceAuditLogService->addBackendLogFromLineItemAuditLogService(
                $auditLogValue,
                $references
            );

            return;
        }

        $this->orderClearanceAuditLogService->addLogFromLineItemAuditLogService(
            $auditLogValue,
            $references,
            $identity
        );
    }
}
