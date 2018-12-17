<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Frontend;

use Shopware\B2B\Acl\Framework\AclGrantContextProviderChain;
use Shopware\B2B\Address\Bridge\ConfigService;
use Shopware\B2B\Address\Framework\AddressCrudService;
use Shopware\B2B\Address\Framework\AddressEntity;
use Shopware\B2B\Address\Framework\AddressRepositoryInterface;
use Shopware\B2B\Address\Framework\AddressSearchStruct;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\EmptyForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Company\Frontend\CompanyFilterResolver;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class AddressController
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressCrudService
     */
    private $addressCrudService;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var CompanyFilterResolver
     */
    private $companyFilterResolver;

    /**
     * @var AclGrantContextProviderChain
     */
    private $grantContextProviderChain;

    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @param AuthenticationService $authenticationService
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressCrudService $addressCrudService
     * @param GridHelper $gridHelper
     * @param CompanyFilterResolver $companyFilterResolver
     * @param AclGrantContextProviderChain $grantContextProviderChain
     * @param ConfigService $configService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AddressRepositoryInterface $addressRepository,
        AddressCrudService $addressCrudService,
        GridHelper $gridHelper,
        CompanyFilterResolver $companyFilterResolver,
        AclGrantContextProviderChain $grantContextProviderChain,
        ConfigService $configService
    ) {
        $this->authenticationService = $authenticationService;
        $this->addressRepository = $addressRepository;
        $this->addressCrudService = $addressCrudService;
        $this->gridHelper = $gridHelper;
        $this->companyFilterResolver = $companyFilterResolver;
        $this->grantContextProviderChain = $grantContextProviderChain;
        $this->configService = $configService;
    }

    public function indexAction()
    {
        // nth
    }

    /**
     * @param Request $request
     * @return array
     */
    public function billingAction(Request $request): array
    {
        return $this->createGridResponse($request, AddressEntity::TYPE_BILLING, $this->gridHelper);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function shippingAction(Request $request): array
    {
        return $this->createGridResponse($request, AddressEntity::TYPE_SHIPPING, $this->gridHelper);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function newAction(Request $request): array
    {
        $validationResponse = $this->gridHelper->getValidationResponse('address');

        return array_merge([
            'type' => $request->getParam('type', 'billing'),
            'countryList' => $this->addressRepository->getCountryList(),
            'grantContext' => $request->requireParam('grantContext'),
            'requiredFields' => $this->configService->getRequiredFields(),
        ], $validationResponse);
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function createAction(Request $request)
    {
        $request->checkPost();

        $post = $request->getPost();

        $serviceRequest = $this->addressCrudService
            ->createNewRecordRequest($post);

        $identity = $this->authenticationService
            ->getIdentity();

        $grantContext = $this->grantContextProviderChain
            ->fetchOneByIdentifier($request->requireParam('grantContext'), $identity->getOwnershipContext());

        try {
            $address = $this->addressCrudService
                ->create($serviceRequest, $identity->getOwnershipContext(), $post['type'], $grantContext);
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);
            throw new B2bControllerForwardException('new');
        }

        throw new B2bControllerForwardException('detail', null, null, ['id' => $address->id]);
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function removeAction(Request $request)
    {
        $request->checkPost();
        $identity = $this->authenticationService->getIdentity();
        $serviceRequest = $this->addressCrudService
            ->createExistingRecordRequest($request->getPost());

        try {
            $this->addressCrudService->remove($serviceRequest, $identity->getOwnershipContext());
        } catch (CanNotRemoveExistingRecordException $e) {
            // nth
        }

        throw new EmptyForwardException();
    }

    /**
     * @param Request $request
     * @return array
     */
    public function detailAction(Request $request): array
    {
        $addressId = (int) $request->requireParam('id');
        $identity = $this->authenticationService->getIdentity();

        $address = $this->addressRepository->fetchOneById($addressId, $identity);

        $validationResponse = $this->gridHelper->getValidationResponse('address');

        return array_merge([
            'countryList' => $this->addressRepository->getCountryList(),
            'type' => $address->type,
            'address' => $address,
            'requiredFields' => $this->configService->getRequiredFields(),
        ], $validationResponse);
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function updateAction(Request $request)
    {
        $request->checkPost();

        $post = $request->getPost();
        $serviceRequest = $this->addressCrudService
            ->createExistingRecordRequest($post);

        $ownershipContext = $this->authenticationService->getIdentity()
            ->getOwnershipContext();

        try {
            $this->addressCrudService
                ->update($serviceRequest, $ownershipContext, $post['type']);
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);
        }

        throw new B2bControllerForwardException('detail', null, null, ['id' => $post['id']]);
    }

    /**
     * @internal
     * @param Request $request
     * @param string $addressType
     * @param GridHelper $gridHelper
     * @return array
     */
    protected function createGridResponse(Request $request, string $addressType, GridHelper $gridHelper): array
    {
        $searchStruct = new AddressSearchStruct();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        $this->companyFilterResolver
            ->extractGrantContextFromRequest($request, $searchStruct, $ownershipContext);

        $billingAddresses = $this->addressRepository
            ->fetchList($addressType, $ownershipContext, $searchStruct);

        $totalCount = $this->addressRepository
            ->fetchTotalCount($addressType, $ownershipContext, $searchStruct);

        $maxPage = $gridHelper
            ->getMaxPage($totalCount);

        $currentPage = $gridHelper->getCurrentPage($request);

        $gridState = $gridHelper
            ->getGridState($request, $searchStruct, $billingAddresses, $maxPage, $currentPage);

        return array_merge(
            [
                'gridState' => $gridState,
                'grantContext' => $searchStruct->aclGrantContext->getIdentifier(),
            ],
            $this->companyFilterResolver->getViewFilterVariables($searchStruct)
        );
    }
}
