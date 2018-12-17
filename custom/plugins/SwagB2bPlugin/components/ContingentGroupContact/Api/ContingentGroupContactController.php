<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroupContact\Api;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Contact\Framework\ContactAuthenticationIdentityLoader;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupRepository;
use Shopware\B2B\ContingentGroupContact\Framework\ContingentGroupContactAssignmentService;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;

class ContingentGroupContactController
{
    /**
     * @var ContingentGroupContactAssignmentService
     */
    private $contingentGroupContactAssignmentService;

    /**
     * @var ContingentGroupRepository
     */
    private $contingentGroupRepository;

    /**
     * @var ContactAuthenticationIdentityLoader
     */
    private $contactRepository;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @param ContingentGroupContactAssignmentService $contingentGroupContactAssignmentService
     * @param ContingentGroupRepository $contingentGroupRepository
     * @param ContactAuthenticationIdentityLoader $contactRepository
     * @param LoginContextService $loginContextService
     */
    public function __construct(
        ContingentGroupContactAssignmentService $contingentGroupContactAssignmentService,
        ContingentGroupRepository $contingentGroupRepository,
        ContactAuthenticationIdentityLoader $contactRepository,
        LoginContextService $loginContextService
    ) {
        $this->contingentGroupContactAssignmentService = $contingentGroupContactAssignmentService;
        $this->contingentGroupRepository = $contingentGroupRepository;
        $this->contactRepository = $contactRepository;
        $this->loginContextService = $loginContextService;
    }

    /**
     * @param string $debtorEmail
     * @param string $email
     * @return array
     */
    public function getListAction(string $debtorEmail, string $email): array
    {
        $contactIdentity = $this->contactRepository
            ->fetchIdentityByEmail($email, $this->loginContextService)
            ->getOwnershipContext();

        $contingentGroupContacts = $this->contingentGroupRepository
            ->fetchContingentGroupIdsForContact($contactIdentity->identityId);

        $totalCount = count($contingentGroupContacts);

        return ['success' => true, 'contingentGroupContacts' => $contingentGroupContacts, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param string $email
     * @param Request $request
     * @return array
     */
    public function createAction(string $debtorEmail, string $email, Request $request): array
    {
        $contactIdentity = $this->contactRepository
            ->fetchIdentityByEmail($email, $this->loginContextService)
            ->getOwnershipContext();

        $this->contingentGroupContactAssignmentService
            ->assign((int) $request->getParam('contingentGroupId'), $contactIdentity->identityId);

        return ['success' => true];
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @param string $email
     * @return array
     */
    public function removeAction(string $debtorEmail, string $email, Request $request): array
    {
        $contactIdentity = $this->contactRepository
            ->fetchIdentityByEmail($email, $this->loginContextService)
            ->getOwnershipContext();

        $this->contingentGroupContactAssignmentService
            ->removeAssignment((int) $request->getParam('contingentGroupId'), $contactIdentity->identityId);

        return ['success' => true];
    }
}
