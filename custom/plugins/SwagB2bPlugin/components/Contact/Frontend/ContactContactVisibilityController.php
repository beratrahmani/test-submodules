<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\Contact\Framework\ContactSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class ContactContactVisibilityController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var AclRepository
     */
    private $contactAclRepository;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var AclAccessExtensionService
     */
    private $aclAccessExtensionService;

    /**
     * @param AuthenticationService $authenticationService
     * @param AclRepository $addressAclRepository
     * @param ContactRepository $contactRepository
     * @param GridHelper $gridHelper
     * @param AclAccessExtensionService $aclAccessExtensionService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AclRepository $addressAclRepository,
        ContactRepository $contactRepository,
        GridHelper $gridHelper,
        AclAccessExtensionService $aclAccessExtensionService
    ) {
        $this->authenticationService = $authenticationService;
        $this->contactAclRepository = $addressAclRepository;
        $this->contactRepository = $contactRepository;
        $this->gridHelper = $gridHelper;
        $this->aclAccessExtensionService = $aclAccessExtensionService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $contactId = (int) $request->requireParam('contactId');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $baseContact = $this->contactRepository
            ->fetchOneById($contactId, $ownershipContext);

        $searchStruct = new ContactSearchStruct();
        $this->gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        $contacts = $this->contactRepository
            ->fetchList($ownershipContext, $searchStruct);

        $this->aclAccessExtensionService
            ->extendEntitiesWithAssignment($this->contactAclRepository, $baseContact, $contacts);

        $this->aclAccessExtensionService
            ->extendEntitiesWithIdentityOwnership($this->contactAclRepository, $ownershipContext, $contacts);

        $count = $this->contactRepository
            ->fetchTotalCount($ownershipContext, $searchStruct);

        $maxPage = $this->gridHelper->getMaxPage($count);
        $currentPage = $this->gridHelper->getCurrentPage($request);

        return [
            'gridState' => $this->gridHelper->getGridState($request, $searchStruct, $contacts, $maxPage, $currentPage),
            'contacts' => $contacts,
            'baseContact' => $baseContact,
        ];
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function assignAction(Request $request)
    {
        $request->checkPost('index', ['contactId' => $request->getParam('baseContactId')]);
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $contactId = (int) $request->requireParam('contactId');
        $baseContactId = (int) $request->requireParam('baseContactId');
        $grantable = (bool) $request->getParam('grantable', false);

        $baseContact = $this->contactRepository
            ->fetchOneById($baseContactId, $ownershipContext);

        if ($request->getParam('allow', false)) {
            $this->contactAclRepository
                ->allow($baseContact, $contactId, $grantable);
        } else {
            $this->contactAclRepository
                ->deny($baseContact, $contactId);
        }

        throw new B2bControllerForwardException('index', null, null, ['contactId' => $baseContactId]);
    }
}
