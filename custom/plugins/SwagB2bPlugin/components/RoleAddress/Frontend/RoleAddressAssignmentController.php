<?php declare(strict_types=1);

namespace Shopware\B2B\RoleAddress\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Address\Framework\AddressRepositoryInterface;
use Shopware\B2B\Address\Framework\AddressSearchStruct;
use Shopware\B2B\Common\Controller\EmptyForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\RoleAddress\Framework\RoleAddressAssignmentService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class RoleAddressAssignmentController
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AclRepository
     */
    private $addressAclRepository;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var AclAccessExtensionService
     */
    private $aclAccessExtensionService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var RoleAddressAssignmentService
     */
    private $assignmentService;

    /**
     * @param AuthenticationService $authenticationService
     * @param AddressRepositoryInterface $addressRepository
     * @param AclRepository $addressAclRepository
     * @param RoleRepository $roleRepository
     * @param GridHelper $gridHelper
     * @param AclAccessExtensionService $aclAccessExtensionService
     * @param RoleAddressAssignmentService $assignmentService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AddressRepositoryInterface $addressRepository,
        AclRepository $addressAclRepository,
        RoleRepository $roleRepository,
        GridHelper $gridHelper,
        AclAccessExtensionService $aclAccessExtensionService,
        RoleAddressAssignmentService $assignmentService
    ) {
        $this->authenticationService = $authenticationService;
        $this->addressRepository = $addressRepository;
        $this->addressAclRepository = $addressAclRepository;
        $this->roleRepository = $roleRepository;
        $this->gridHelper = $gridHelper;
        $this->aclAccessExtensionService = $aclAccessExtensionService;
        $this->assignmentService = $assignmentService;
    }

    /**
     * @param  Request $request
     * @return array
     */
    public function gridAction(Request $request): array
    {
        $addressType = $request->requireParam('type');
        $roleId = (int) $request->requireParam('roleId');

        $searchStruct = new AddressSearchStruct();

        $this->gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        $addresses = $this->addressRepository
            ->fetchList($addressType, $ownershipContext, $searchStruct);

        $this->aclAccessExtensionService
            ->extendEntitiesWithAssignment($this->addressAclRepository, $role, $addresses);

        $this->aclAccessExtensionService
            ->extendEntitiesWithIdentityOwnership($this->addressAclRepository, $ownershipContext, $addresses);

        $count = $this->addressRepository
            ->fetchTotalCount($addressType, $ownershipContext, $searchStruct);

        $maxPage = $this->gridHelper->getMaxPage($count);
        $currentPage = $this->gridHelper->getCurrentPage($request);

        $gridState = $this->gridHelper
            ->getGridState($request, $searchStruct, $addresses, $maxPage, $currentPage);

        return [
            'gridState' => $gridState,
            'type' => $addressType,
            'addresses' => $addresses,
            'roleId' => (int) $request->requireParam('roleId'),
        ];
    }

    /**
     * @param  Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     * @return array
     */
    public function assignAction(Request $request): array
    {
        $type = $request->requireParam('type');
        $roleId = (int) $request->requireParam('roleId');
        $request->checkPost('grid', [
            'type' => $type,
            'roleId' => $roleId,
        ]);

        $crudRequest = $this->assignmentService->createAssignRecordRequest($request->getPost());
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        try {
            if ($request->getParam('allow', false)) {
                $this->assignmentService->allow($crudRequest, $ownershipContext);
            } else {
                $this->assignmentService->deny($crudRequest, $ownershipContext);
            }
        } catch (ValidationException $validationException) {
            $this->gridHelper->pushValidationException($validationException);

            return $this->gridHelper->getValidationResponse('role');
        }

        throw new EmptyForwardException();
    }
}
