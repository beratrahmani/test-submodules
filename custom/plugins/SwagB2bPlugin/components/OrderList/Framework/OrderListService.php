<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Framework;

use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListService;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceValidationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderListService
{
    /**
     * @var LineItemListService
     */
    private $lineItemListService;

    /**
     * @var OrderListRepository
     */
    private $orderListRepository;

    /**
     * @var LineItemReferenceValidationService
     */
    private $lineItemReferenceValidationService;

    /**
     * @param LineItemListService $lineItemListService
     * @param OrderListRepository $orderListRepository
     * @param LineItemReferenceValidationService $lineItemReferenceValidationService
     */
    public function __construct(
        LineItemListService $lineItemListService,
        OrderListRepository $orderListRepository,
        LineItemReferenceValidationService $lineItemReferenceValidationService
    ) {
        $this->lineItemListService = $lineItemListService;
        $this->orderListRepository = $orderListRepository;
        $this->lineItemReferenceValidationService = $lineItemReferenceValidationService;
    }

    /**
     * @param OrderListEntity $orderList
     * @param string $cartId
     * @param OwnershipContext $ownershipContext
     * @param CurrencyContext $currencyContext
     * @return OrderListEntity
     */
    public function addListThroughCart(
        OrderListEntity $orderList,
        string $cartId,
        OwnershipContext $ownershipContext,
        CurrencyContext $currencyContext
    ): OrderListEntity {
        $list = $this->lineItemListService->createListThroughCartId($cartId, $ownershipContext);

        $list->id = $orderList->listId;

        $this->lineItemListService->updateListReferences($list, $currencyContext, $ownershipContext);

        $this->lineItemListService
            ->updateListPrices($list, $ownershipContext);

        return $orderList;
    }

    /**
     * @param int $orderListId
     * @param LineItemList $list
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return OrderListEntity
     */
    public function addListThroughLineItemList(
        int $orderListId,
        LineItemList $list,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): OrderListEntity {
        $orderList = $this->orderListRepository
            ->fetchOneById($orderListId, $currencyContext, $ownershipContext);

        $list->id = $orderList->listId;

        $this->lineItemListService->updateListReferences($list, $currencyContext, $ownershipContext);

        $this->lineItemListService
            ->updateListPrices($list, $ownershipContext);

        return $orderList;
    }

    /**
     * @param array $product
     * @return LineItemReference
     */
    public function createReferenceFromProductRequest(array $product): LineItemReference
    {
        $reference = new LineItemReference();

        $reference->referenceNumber = $product['referenceNumber'];
        $reference->quantity = (int) $product['quantity'];
        $reference->mode = 0;

        $validator = $this->lineItemReferenceValidationService
            ->createReferenceValidation($reference);

        $violations = $validator->getViolations();

        if (count($violations)) {
            throw new ValidationException($reference, $violations, 'Validation violations detected, can not proceed:', 400);
        }

        return $reference;
    }
}
