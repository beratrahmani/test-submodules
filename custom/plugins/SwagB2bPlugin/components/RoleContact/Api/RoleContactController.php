<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContact\Api;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Contact\Framework\ContactAuthenticationIdentityLoader;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\RoleContact\Framework\RoleContactAssignmentService;
use Shopware\B2B\RoleContact\Framework\RoleContactService;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;

class RoleContactController
{
    /**
     * @var RoleContactService
     */
    private $roleContactService;

    /**
     * @var RoleContactAssignmentService
     */
    private $roleContactAssignmentService;

    /**
     * @var ContactAuthenticationIdentityLoader
     */
    private $contactRepository;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var DebtorAuthenticationIdentityLoader
     */
    private $contextAuthenticationLoader;

    /**
     * @param RoleContactService $roleContactService
     * @param RoleContactAssignmentService $roleContactAssignmentService
     * @param ContactAuthenticationIdentityLoader $contactRepository
     * @param LoginContextService $loginContextService
     * @param DebtorAuthenticationIdentityLoader $contextAuthenticationLoader
     */
    public function __construct(
        RoleContactService $roleContactService,
        RoleContactAssignmentService $roleContactAssignmentService,
        ContactAuthenticationIdentityLoader $contactRepository,
        LoginContextService $loginContextService,
        DebtorAuthenticationIdentityLoader $contextAuthenticationLoader
    ) {
        $this->roleContactService = $roleContactService;
        $this->roleContactAssignmentService = $roleContactAssignmentService;
        $this->contactRepository = $contactRepository;
        $this->loginContextService = $loginContextService;
        $this->contextAuthenticationLoader = $contextAuthenticationLoader;
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

        $roles = $this->roleContactService
            ->getActiveRolesByContactId($contactIdentity->identityId);

        $totalCount = count($roles);

        return ['success' => true, 'roles' => $roles, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param string $email
     * @param Request $request
     * @return array
     */
    public function createAction(string $debtorEmail, string $email, Request $request): array
    {
        $contactOwnership = $this->contactRepository
            ->fetchIdentityByEmail($email, $this->loginContextService)
            ->getOwnershipContext();
        $debtorOwnershipContext =  $this->contextAuthenticationLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $this->roleContactAssignmentService
            ->assign($debtorOwnershipContext, (int) $request->getParam('roleId'), $contactOwnership->identityId);

        return ['success' => true];
    }

    /**
     * @param string $debtorEmail
     * @param string $email
     * @param Request $request
     * @return array
     */
    public function removeAction(string $debtorEmail, string $email, Request $request): array
    {
        $contactIdentity = $this->contactRepository
            ->fetchIdentityByEmail($email, $this->loginContextService)
            ->getOwnershipContext();
        $debtorOwnershipContext =  $this->contextAuthenticationLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $this->roleContactAssignmentService
            ->removeAssignment($debtorOwnershipContext, (int) $request->getParam('roleId'), $contactIdentity->identityId);

        return ['success' => true];
    }
}
