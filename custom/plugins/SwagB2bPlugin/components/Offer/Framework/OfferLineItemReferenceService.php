<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\LineItemList\Framework\LineItemReferenceSearchStruct;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceService;
use Shopware\B2B\ProductName\Framework\ProductNameAware;
use Shopware\B2B\ProductName\Framework\ProductNameService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OfferLineItemReferenceService
{
    /**
     * @var OfferLineItemReferenceRepository
     */
    private $lineItemReferenceRepository;

    /**
     * @var ProductNameService
     */
    private $productNameService;

    /**
     * @var LineItemReferenceService
     */
    private $lineItemReferenceService;

    /**
     * @param OfferLineItemReferenceRepository $lineItemReferenceRepository
     * @param ProductNameAware $productNameAware
     * @param LineItemReferenceService $lineItemReferenceService
     */
    public function __construct(
        OfferLineItemReferenceRepository $lineItemReferenceRepository,
        ProductNameService $productNameAware,
        LineItemReferenceService $lineItemReferenceService
    ) {
        $this->lineItemReferenceRepository = $lineItemReferenceRepository;
        $this->productNameService = $productNameAware;
        $this->lineItemReferenceService = $lineItemReferenceService;
    }

    /**
     * @param int $listId
     * @param LineItemReferenceSearchStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     * @return OfferLineItemReferenceEntity[]
     */
    public function fetchLineItemsReferencesWithProductNames(
        int $listId,
        LineItemReferenceSearchStruct $searchStruct,
        OwnershipContext $ownershipContext
    ): array {
        $list = $this->lineItemReferenceRepository
            ->fetchList($listId, $searchStruct, $ownershipContext);

        foreach ($list as $listItem) {
            $this->productNameService->translateProductName($listItem);
        }

        $this->lineItemReferenceService->mapCustomOrderNumbers($list, $ownershipContext);

        return $list;
    }
}
