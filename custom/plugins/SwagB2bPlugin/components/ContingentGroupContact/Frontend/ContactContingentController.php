<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroupContact\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupRepository;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupSearchStruct;
use Shopware\B2B\ContingentGroupContact\Framework\ContingentGroupContactAssignmentService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class ContactContingentController
{
    /**
     * @var ContingentGroupRepository
     */
    private $contingentGroupRepository;

    /**
     * @var ContingentGroupContactAssignmentService
     */
    private $contingentGroupAssignService;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var AclAccessExtensionService
     */
    private $aclAccessExtensionService;

    /**
     * @var AclRepository
     */
    private $contactContingentGroupAclRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param AuthenticationService $authenticationService
     * @param ContingentGroupRepository $contingentGroupRepository
     * @param ContingentGroupContactAssignmentService $contingentGroupAssignmentService
     * @param GridHelper $gridHelper
     * @param ContactRepository $contactRepository
     * @param AclAccessExtensionService $aclAccessExtensionService
     * @param AclRepository $contactContingentGroupAclRepository
     */
    public function __construct(
        AuthenticationService $authenticationService,
        ContingentGroupRepository $contingentGroupRepository,
        ContingentGroupContactAssignmentService $contingentGroupAssignmentService,
        GridHelper $gridHelper,
        ContactRepository $contactRepository,
        AclAccessExtensionService $aclAccessExtensionService,
        AclRepository $contactContingentGroupAclRepository
    ) {
        $this->contingentGroupRepository = $contingentGroupRepository;
        $this->contingentGroupAssignService = $contingentGroupAssignmentService;
        $this->gridHelper = $gridHelper;
        $this->contactRepository = $contactRepository;
        $this->aclAccessExtensionService = $aclAccessExtensionService;
        $this->contactContingentGroupAclRepository = $contactContingentGroupAclRepository;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $contactId = (int) $request->requireParam('contactId');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $contact = $this->contactRepository->fetchOneById($contactId, $ownershipContext);

        $searchStruct = new ContingentGroupSearchStruct();

        $this->gridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        $contingentGroups = $this->contingentGroupRepository
            ->fetchListByContactId($ownershipContext, $searchStruct, $contactId);

        $this->aclAccessExtensionService
            ->extendEntitiesWithAssignment($this->contactContingentGroupAclRepository, $contact, $contingentGroups);

        $this->aclAccessExtensionService
            ->extendEntitiesWithIdentityOwnership($this->contactContingentGroupAclRepository, $ownershipContext, $contingentGroups);

        $groupCount = $this->contingentGroupRepository->fetchTotalCount($ownershipContext, $searchStruct);

        $maxPage = $this->gridHelper->getMaxPage($groupCount);

        $currentPage = (int) $this->gridHelper->getCurrentPage($request);

        $gridState = $this->gridHelper
            ->getGridState($request, $searchStruct, $contingentGroups, $maxPage, $currentPage);

        return [
            'contactId' => $contactId,
            'gridState' => $gridState,
        ];
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function assignAction(Request $request)
    {
        $request->checkPost();

        $contingentId = (int) $request->requireParam('contingentGroupId');
        $contactId = (int) $request->requireParam('contactId');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $contact = $this->contactRepository
            ->fetchOneById($contactId, $ownershipContext);

        if (!$request->getParam('allow')) {
            $this->contingentGroupAssignService
                ->removeAssignment($contingentId, $contactId);

            $this->contactContingentGroupAclRepository
                ->deny($contact, $contingentId);

            throw new B2bControllerForwardException('index', null, null, ['contactId' => $contactId]);
        }

        $this->contactContingentGroupAclRepository
            ->allow($contact, $contingentId, (bool) $request->getParam('grantable'));

        if (!$request->getParam('assignmentId')) {
            $this->contingentGroupAssignService
                ->assign($contingentId, $contactId);
        }

        throw new B2bControllerForwardException('index', null, null, ['contactId' => $contactId]);
    }
}
