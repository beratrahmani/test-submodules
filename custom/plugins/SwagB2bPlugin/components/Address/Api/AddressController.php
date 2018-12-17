<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Api;

use Shopware\B2B\Acl\Framework\AclGrantContext;
use Shopware\B2B\Acl\Framework\AclGrantContextProviderChain;
use Shopware\B2B\Address\Framework\AddressCrudService;
use Shopware\B2B\Address\Framework\AddressRepositoryInterface;
use Shopware\B2B\Address\Framework\AddressSearchStruct;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class AddressController
{
    /**
     * @var GridHelper
     */
    private $requestHelper;

    /**
     * @var DebtorAuthenticationIdentityLoader
     */
    private $identityLoader;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressCrudService
     */
    private $addressCrudService;

    /**
     * @var AclGrantContextProviderChain
     */
    private $grantContextProviderChain;

    /**
     * @param AddressRepositoryInterface $addressRepository
     * @param GridHelper $requestHelper
     * @param AddressCrudService $addressCrudService
     * @param DebtorAuthenticationIdentityLoader $identityLoader
     * @param LoginContextService $loginContextService
     * @param AclGrantContextProviderChain $grantContextProviderChain
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        GridHelper $requestHelper,
        AddressCrudService $addressCrudService,
        DebtorAuthenticationIdentityLoader $identityLoader,
        LoginContextService $loginContextService,
        AclGrantContextProviderChain $grantContextProviderChain
    ) {
        $this->requestHelper = $requestHelper;
        $this->identityLoader = $identityLoader;
        $this->loginContextService = $loginContextService;
        $this->addressRepository = $addressRepository;
        $this->addressCrudService = $addressCrudService;
        $this->grantContextProviderChain = $grantContextProviderChain;
    }

    /**
     * @param string $debtorEmail
     * @param string $addressType
     * @param Request $request
     * @return array
     */
    public function getListAction(string $debtorEmail, string $addressType, Request $request): array
    {
        $search = new AddressSearchStruct();

        $this->requestHelper
            ->extractSearchDataInRestApi($request, $search);

        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $addresses = $this->addressRepository->fetchList($addressType, $ownershipContext, $search);

        $totalCount = $this->addressRepository
            ->fetchTotalCount($addressType, $ownershipContext, $search);

        return ['success' => true, 'addresses' => $addresses, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param int $addressId
     * @return array
     */
    public function getAction(string $debtorEmail, int $addressId): array
    {
        $identity = $this->identityLoader
            ->fetchIdentityByEmail(
                $debtorEmail,
                $this->loginContextService
            );

        $address = $this->addressRepository->fetchOneById($addressId, $identity);

        return ['success' => true, 'address' => $address];
    }

    /**
     * @param string $debtorEmail
     * @param string $addressType
     * @param Request $request
     * @return array
     */
    public function createAction(
        string $debtorEmail,
        string $addressType,
        Request $request
    ): array {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $aclGrantContext = $this->extractGrantContext($request, $ownershipContext);

        $data = $request->getPost();

        $newRecord = $this->addressCrudService
            ->createNewRecordRequest($data);

        $address = $this->addressCrudService
            ->create($newRecord, $ownershipContext, $addressType, $aclGrantContext);

        return ['success' => true, 'address' => $address];
    }

    /**
     * @param string $debtorEmail
     * @param int $addressId
     * @param string $addressType
     * @param Request $request
     * @return array
     */
    public function updateAction(
        string $debtorEmail,
        int $addressId,
        string $addressType,
        Request $request
    ): array {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $data = $request->getPost();
        $data['id'] = $addressId;

        $existingRecord = $this->addressCrudService
            ->createExistingRecordRequest($data);

        $address = $this->addressCrudService
            ->update($existingRecord, $ownershipContext, $addressType);

        return ['success' => true, 'address' => $address];
    }

    /**
     * @param string $debtorEmail
     * @param int $addressId
     * @return array
     */
    public function removeAction(string $debtorEmail, int $addressId): array
    {
        $existingRecord = $this->addressCrudService
            ->createExistingRecordRequest(['id' => $addressId]);

        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $this->addressCrudService->remove($existingRecord, $ownershipContext);

        return ['success' => true, 'removedAddress' => $addressId];
    }

    /**
     * @internal
     * @param string $debtorEmail
     * @return OwnershipContext
     */
    protected function getDebtorOwnershipContextByEmail(string $debtorEmail): OwnershipContext
    {
        return $this->identityLoader
            ->fetchIdentityByEmail(
                $debtorEmail,
                $this->loginContextService
            )
            ->getOwnershipContext();
    }

    /**
     * @internal
     * @param Request $request
     * @param OwnershipContext $ownershipContext
     * @throws \InvalidArgumentException
     * @return AclGrantContext
     */
    protected function extractGrantContext(Request $request, OwnershipContext $ownershipContext): AclGrantContext
    {
        $grantContextIdentifier = $request->requireParam('grantContextIdentifier');

        return $this->grantContextProviderChain->fetchOneByIdentifier($grantContextIdentifier, $ownershipContext);
    }
}
