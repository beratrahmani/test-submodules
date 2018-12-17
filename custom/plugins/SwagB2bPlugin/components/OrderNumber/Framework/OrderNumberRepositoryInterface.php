<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Framework;

use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Repository\SearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface OrderNumberRepositoryInterface extends GridRepository
{
    /**
     * @param SearchStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     * @return array
     */
    public function fetchList(SearchStruct $searchStruct, OwnershipContext $ownershipContext): array;

    /**
     * @param SearchStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     * @return int
     */
    public function fetchTotalCount(SearchStruct $searchStruct, OwnershipContext $ownershipContext): int;

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @return bool
     */
    public function isCustomOrderNumberAvailable(OrderNumberEntity $orderNumberEntity): bool;

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @return OrderNumberEntity
     */
    public function updateOrderNumber(OrderNumberEntity $orderNumberEntity): OrderNumberEntity;

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @return OrderNumberEntity
     */
    public function createOrderNumber(OrderNumberEntity $orderNumberEntity): OrderNumberEntity;

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @return OrderNumberEntity
     */
    public function removeOrderNumber(OrderNumberEntity $orderNumberEntity): OrderNumberEntity;

    /**
     * @param OwnershipContext $context
     * @return OrderNumberEntity[]
     */
    public function fetchAllProductsForExport(OwnershipContext $context): array;

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @return bool
     */
    public function isOrderNumberUnique(OrderNumberEntity $orderNumberEntity): bool;

    /**
     * @param OwnershipContext $ownershipContext
     */
    public function clearOrderNumbers(OwnershipContext $ownershipContext);

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @throws NotFoundException
     * @return int
     */
    public function fetchDetailsId(OrderNumberEntity $orderNumberEntity): int;

    /**
     * @param string[] $orderNumbers
     * @param OwnershipContext $ownershipContext
     * @return string[]
     */
    public function fetchCustomOrderNumbers(array $orderNumbers, OwnershipContext $ownershipContext): array;

    /**
     * @param string $orderNumber
     * @param OwnershipContext $ownershipContext
     * @return string
     */
    public function fetchCustomOrderNumber(string $orderNumber, OwnershipContext $ownershipContext): string;

    /**
     * @param string[] $numbers
     * @param OwnershipContext $ownerShipContext
     * @return string[]
     */
    public function fetchOriginalOrderNumbers(array $numbers, OwnershipContext $ownerShipContext): array;

    /**
     * @param string $referenceNumber
     * @param OwnershipContext $ownershipContext
     * @return string
     */
    public function fetchOriginalOrderNumber(string $referenceNumber, OwnershipContext $ownershipContext): string;
}
