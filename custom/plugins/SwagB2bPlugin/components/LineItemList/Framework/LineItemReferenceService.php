<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Framework;

use Shopware\B2B\OrderNumber\Framework\OrderNumberRepositoryInterface;
use Shopware\B2B\ProductName\Framework\ProductNameService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class LineItemReferenceService
{
    /**
     * @var LineItemReferenceRepository
     */
    private $lineItemReferenceRepository;

    /**
     * @var OrderNumberRepositoryInterface
     */
    private $orderNumberRepository;

    /**
     * @var ProductNameService
     */
    private $productNameService;

    /**
     * @param LineItemReferenceRepository $lineItemReferenceRepository
     * @param OrderNumberRepositoryInterface $orderNumberRepository
     * @param ProductNameService $productNameService
     */
    public function __construct(
        LineItemReferenceRepository $lineItemReferenceRepository,
        OrderNumberRepositoryInterface $orderNumberRepository,
        ProductNameService $productNameService
    ) {
        $this->lineItemReferenceRepository = $lineItemReferenceRepository;
        $this->orderNumberRepository = $orderNumberRepository;
        $this->productNameService = $productNameService;
    }

    /**
     * @param int $listId
     * @param $searchStruct
     * @param OwnershipContext $ownershipContext
     * @return LineItemReference[]
     */
    public function fetchLineItemsReferencesWithProductNames(
        int $listId,
        $searchStruct,
        OwnershipContext $ownershipContext
    ): array {
        $list = $this->lineItemReferenceRepository
            ->fetchList($listId, $searchStruct);

        foreach ($list as $listItem) {
            $this->productNameService->translateProductName($listItem);
        }

        $this->mapCustomOrderNumbers($list, $ownershipContext);

        return $list;
    }

    /**
     * @param LineItemList $lineItemList
     * @return LineItemList
     */
    public function fetchLineItemListProductNames(LineItemList $lineItemList): LineItemList
    {
        foreach ($lineItemList->references as $listItem) {
            $this->productNameService->translateProductName($listItem);
        }

        return $lineItemList;
    }

    /**
     * @param LineItemReference[] $list
     * @param OwnershipContext $ownershipContext
     */
    public function mapCustomOrderNumbers(array $list, OwnershipContext $ownershipContext)
    {
        $orderNumbers = array_map(
            function (LineItemReference $listItem) {
                return $listItem->referenceNumber;
            },
            $list
        );

        $customOrderNumbers = $this->orderNumberRepository->fetchCustomOrderNumbers($orderNumbers, $ownershipContext);

        foreach ($list as $listItem) {
            if (isset($customOrderNumbers[$listItem->referenceNumber])) {
                $listItem->referenceNumber = $customOrderNumbers[$listItem->referenceNumber];
            }
        }
    }
}
