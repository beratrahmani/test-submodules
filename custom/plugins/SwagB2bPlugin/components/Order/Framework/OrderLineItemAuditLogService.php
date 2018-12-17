<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

use Shopware\B2B\AuditLog\Framework\AuditLogValueBasicEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueLineItemAddEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueLineItemCommentEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueLineItemQuantityEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueLineItemRemoveEntity;
use Shopware\B2B\AuditLog\Framework\RefusingToInsertDuplicatedLogEntryException;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\ProductName\Framework\ProductNameService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OrderLineItemAuditLogService
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
     * @param string $comment
     */
    public function createLineItemComment(int $listId, LineItemReference $item, string $comment)
    {
        $productName = $this->getProductName($item);

        $auditLogValue = new AuditLogValueLineItemCommentEntity();
        $auditLogValue->oldValue = $item->comment;
        $auditLogValue->newValue = $comment;
        $auditLogValue->orderNumber = $item->referenceNumber;
        $auditLogValue->productName = $productName;

        $this->createAuditLogEntry($listId, $auditLogValue);
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
     * @param LineItemReference $item
     */
    public function createAddLineItem(int $listId, LineItemReference $item)
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
     * @param LineItemReference $item
     * @param int $newQuantity
     */
    public function createLineItemChange(
        int $listId,
        LineItemReference $item,
        int $newQuantity
    ) {
        $productName = $this->getProductName($item);

        $auditLogValue = new AuditLogValueLineItemQuantityEntity();
        $auditLogValue->oldValue = (int) $item->quantity;
        $auditLogValue->newValue = (int) $newQuantity;
        $auditLogValue->orderNumber = $item->referenceNumber;
        $auditLogValue->productName = $productName;

        $this->createAuditLogEntry($listId, $auditLogValue);
    }

    /**
     * @internal
     * @param LineItemReference $item
     * @return string
     */
    protected function getProductName(LineItemReference $item): string
    {
        $this->productNameService->translateProductName($item);

        return (string) $item->name;
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

        $identity = $this->authenticationService
            ->getIdentity();

        try {
            $this->orderClearanceAuditLogService->addLogFromLineItemAuditLogService(
                $auditLogValue,
                $references,
                $identity
            );
        } catch (RefusingToInsertDuplicatedLogEntryException $e) {
            // ignore duplicate log entries
        }
    }
}
