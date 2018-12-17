<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Framework;

use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface AddressRepositoryInterface extends GridRepository
{
    const TABLE_NAME = 's_user_addresses';
    const TABLE_ALIAS = 'address';

    /**
     * @return array
     */
    public function getCountryList(): array;

    /**
     * @param string $type
     * @param OwnershipContext $ownershipContext
     * @param AddressSearchStruct $searchStruct
     * @return AddressEntity[]
     */
    public function fetchList(string $type, OwnershipContext $ownershipContext, AddressSearchStruct $searchStruct): array;

    /**
     * @param string $type
     * @param OwnershipContext $context
     * @param AddressSearchStruct $addressSearchStruct
     * @return int
     */
    public function fetchTotalCount(string $type, OwnershipContext $context, AddressSearchStruct $addressSearchStruct): int;

    /**
     * @param int $id
     * @param Identity $identity
     * @param null|string $addressType optionally filter for a specific type
     * @return AddressEntity
     */
    public function fetchOneById(int $id, Identity $identity, string $addressType = null): AddressEntity;

    /**
     * @param AddressEntity $addressEntity
     * @param string $type
     * @param OwnershipContext $ownershipContext
     * @return AddressEntity
     */
    public function addAddress(AddressEntity $addressEntity, string $type, OwnershipContext $ownershipContext): AddressEntity;

    /**
     * @param AddressEntity $addressEntity
     * @param OwnershipContext $ownershipContext
     * @param string $type
     * @return AddressEntity
     */
    public function updateAddress(AddressEntity $addressEntity, OwnershipContext $ownershipContext, string $type): AddressEntity;

    /**
     * @param AddressEntity $addressEntity
     * @param OwnershipContext $ownershipContext
     * @return AddressEntity
     */
    public function removeAddress(AddressEntity $addressEntity, OwnershipContext $ownershipContext): AddressEntity;
}
