<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Framework;

use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class LineItemListService
{
    /**
     * @var LineItemListRepository
     */
    private $listRepository;

    /**
     * @var LineItemReferenceRepository
     */
    private $referenceRepository;

    /**
     * @var LineItemShopWriterServiceInterface
     */
    private $bridgeService;

    /**
     * @var LineItemCheckoutProviderInterface
     */
    private $checkoutProvider;

    /**
     * @var ProductProviderInterface
     */
    private $productProvider;

    /**
     * @var LineItemReferenceValidationService
     */
    private $lineItemReferenceValidationService;

    /**
     * @param LineItemListRepository $listRepository
     * @param LineItemReferenceRepository $referenceRepository
     * @param LineItemShopWriterServiceInterface $bridgeService
     * @param LineItemCheckoutProviderInterface $checkoutProvider
     * @param ProductProviderInterface $productProvider
     * @param LineItemReferenceValidationService $lineItemReferenceValidationService
     */
    public function __construct(
        LineItemListRepository $listRepository,
        LineItemReferenceRepository $referenceRepository,
        LineItemShopWriterServiceInterface $bridgeService,
        LineItemCheckoutProviderInterface $checkoutProvider,
        ProductProviderInterface $productProvider,
        LineItemReferenceValidationService $lineItemReferenceValidationService
    ) {
        $this->listRepository = $listRepository;
        $this->referenceRepository = $referenceRepository;
        $this->bridgeService = $bridgeService;
        $this->checkoutProvider = $checkoutProvider;
        $this->productProvider = $productProvider;
        $this->lineItemReferenceValidationService = $lineItemReferenceValidationService;
    }

    /**
     * @param int $listId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @param bool $clearBasket
     */
    public function produceCart(
        int $listId,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext,
        bool $clearBasket = false
    ) {
        $list = $this->listRepository->fetchOneListById($listId, $currencyContext, $ownershipContext);

        $this->bridgeService->triggerCart($list, $clearBasket);
    }

    /**
     * @param LineItemList $list
     * @param OwnershipContext $ownershipContext
     * @return LineItemList
     */
    public function createListThroughListObject(LineItemList $list, OwnershipContext $ownershipContext): LineItemList
    {
        $list = $this->listRepository
            ->addList($list, $ownershipContext);

        $this->referenceRepository->syncReferences($list->id, $list->references);

        return $list;
    }

    /**
     * @param LineItemList $list
     * @param OwnershipContext $ownershipContext
     * @return LineItemList
     */
    protected function updateList(LineItemList $list, OwnershipContext $ownershipContext): LineItemList
    {
        $this->listRepository
            ->updateListPrices($list, $ownershipContext);

        $this->referenceRepository->syncReferences($list->id, $list->references);

        return $list;
    }

    /**
     * @param int $listId
     * @param LineItemReference[] $references
     */
    public function addReferencesToList(int $listId, array $references)
    {
        foreach ($references as $reference) {
            $this->referenceRepository
                ->addReference($listId, $reference);
        }
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param LineItemListSource $lineItemListSources
     * @return LineItemList
     */
    public function createListThroughCheckoutSource(OwnershipContext $ownershipContext, LineItemListSource $lineItemListSources): LineItemList
    {
        $list = $this->checkoutProvider->createList($lineItemListSources);

        $list->contextOwnerId = $ownershipContext->contextOwnerId;

        $this->createListThroughListObject($list, $ownershipContext);

        return $list;
    }

    /**
     * @param LineItemList $list
     * @param LineItemListSource $lineItemListSources
     * @param OwnershipContext $ownershipContext
     * @return LineItemList
     */
    public function updateListThroughCheckoutSource(
        LineItemList $list,
        LineItemListSource $lineItemListSources,
        OwnershipContext $ownershipContext
    ): LineItemList {
        $list = $this->checkoutProvider->updateList($list, $lineItemListSources);

        $this->updateList($list, $ownershipContext);

        return $list;
    }

    /**
     * @param string $cartId
     * @param OwnershipContext $ownershipContext
     * @return LineItemList
     */
    public function createListThroughCartId(string $cartId, OwnershipContext $ownershipContext): LineItemList
    {
        $list = $this->checkoutProvider->createListFromCartId($cartId);
        $list->contextOwnerId = $ownershipContext->contextOwnerId;

        return $list;
    }

    /**
     * @param LineItemList $list
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @param bool $replace
     * @return LineItemList
     */
    public function updateListReferences(
        LineItemList $list,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext,
        bool $replace = false
    ): LineItemList {
        if (!$replace) {
            $oldList = $this->listRepository->fetchOneListById($list->id, $currencyContext, $ownershipContext);

            $references = [];
            foreach ($list->references as $reference) {
                try {
                    $oldReference = $oldList->getReferenceByNumber($reference->referenceNumber);
                } catch (\InvalidArgumentException $e) {
                    $references[] = $reference;
                    continue;
                }

                $reference->id = $oldReference->id;
                $reference->quantity += $oldReference->quantity;
                if (!$reference->comment) {
                    $reference->comment = $oldReference->comment;
                }

                $validator = $this->lineItemReferenceValidationService->createUpdateValidation($reference);

                $violations = $validator->getViolations();

                if (count($violations)) {
                    throw new ValidationException($reference, $violations, 'Validation violations detected, can not proceed:', 400);
                }

                $this->referenceRepository->updateReference($list->id, $reference);
                $oldReference->setData($reference->jsonSerialize());
            }

            $list->references = array_merge($oldList->references, $references);
        } else {
            $this->referenceRepository->removeReferenceByListId($list->id);
            $references = $list->references;
        }

        $this->addReferencesToList($list->id, $references);

        $this->updateListPrices($list, $ownershipContext);

        return $list;
    }

    /**
     * @param LineItemList $lineItemList
     * @param OwnershipContext $ownershipContext
     */
    public function updateListPrices(LineItemList $lineItemList, OwnershipContext $ownershipContext)
    {
        $this->productProvider
            ->updateList($lineItemList);

        foreach ($lineItemList->references as $reference) {
            if ($reference->mode !== 0) {
                continue;
            }

            $this->referenceRepository
                ->updatePrices($reference);
        }

        $this->listRepository
            ->updateListPrices($lineItemList, $ownershipContext);
    }

    /**
     * @param int $listId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return LineItemList
     */
    public function updateListPricesById(
        int $listId,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): LineItemList {
        $lineItemList = $this->listRepository
            ->fetchOneListById($listId, $currencyContext, $ownershipContext);

        $this->updateListPrices($lineItemList, $ownershipContext);

        return $lineItemList;
    }
}
