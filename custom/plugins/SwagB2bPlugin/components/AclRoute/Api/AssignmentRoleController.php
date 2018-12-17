<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Api;

use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\Role\Framework\RoleEntity;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class AssignmentRoleController extends ApiAssignmentController
{
    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @param RoleRepository $roleRepository
     * @param AclRepository $aclRepository
     * @param DebtorAuthenticationIdentityLoader $contextIdentityLoader
     * @param LoginContextService $loginContextService
     */
    public function __construct(
        RoleRepository $roleRepository,
        AclRepository $aclRepository,
        DebtorAuthenticationIdentityLoader $contextIdentityLoader,
        LoginContextService $loginContextService
    ) {
        parent::__construct($aclRepository, $contextIdentityLoader, $loginContextService);
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param string $debtorEmail
     * @param int $roleId
     * @return array
     */
    public function getAllGrantAction(string $debtorEmail, int $roleId): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        /** @var RoleEntity $roleEntity */
        $roleEntity = $this->getRoleEntityById($roleId, $ownershipContext);

        return $this->getAllGrant($roleEntity);
    }

    /**
     * @param string $debtorEmail
     * @param int $roleId
     * @return array
     */
    public function getAllAllowedAction(string $debtorEmail, int $roleId): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        /** @var RoleEntity $roleEntity */
        $roleEntity = $this->getRoleEntityById($roleId, $ownershipContext);

        return $this->getAllAllowed($roleEntity);
    }

    /**
     * @param string $debtorEmail
     * @param int $roleId
     * @param int $subjectId
     * @return array
     */
    public function getAllowedAction(string $debtorEmail, int $roleId, int $subjectId): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        /** @var RoleEntity $roleEntity */
        $roleEntity = $this->getRoleEntityById($roleId, $ownershipContext);

        return $this->getAllowed($roleEntity, $subjectId);
    }

    /**
     * @param string $debtorEmail
     * @param int $roleId
     * @param int $subjectId
     * @return array
     */
    public function allowAction(string $debtorEmail, int $roleId, int $subjectId): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        /** @var RoleEntity $roleEntity */
        $roleEntity = $this->getRoleEntityById($roleId, $ownershipContext);

        return $this->allow($roleEntity, $subjectId, false);
    }

    /**
     * @param string $debtorEmail
     * @param int $roleId
     * @param int $subjectId
     * @return array
     */
    public function allowGrantAction(string $debtorEmail, int $roleId, int $subjectId): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        /** @var RoleEntity $roleEntity */
        $roleEntity = $this->getRoleEntityById($roleId, $ownershipContext);

        return $this->allow($roleEntity, $subjectId, true);
    }

    /**
     * @param string $debtorEmail
     * @param int $roleId
     * @param Request $request
     * @return array
     */
    public function multipleAllowAction(string $debtorEmail, int $roleId, Request $request): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        /** @var RoleEntity $roleEntity */
        $roleEntity = $this->getRoleEntityById($roleId, $ownershipContext);

        return $this->multipleAllow($roleEntity, $request->getPost());
    }

    /**
     * @param string $debtorEmail
     * @param int $roleId
     * @param Request $request
     * @return array
     */
    public function multipleDenyAction(string $debtorEmail, int $roleId, Request $request): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        /** @var RoleEntity $roleEntity */
        $roleEntity = $this->getRoleEntityById($roleId, $ownershipContext);

        return $this->multipleDeny($roleEntity, $request->getPost());
    }

    /**
     * @param string $debtorEmail
     * @param int $roleId
     * @param int $subjectId
     * @return array
     */
    public function denyAction(string $debtorEmail, int $roleId, int $subjectId): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        /** @var RoleEntity $roleEntity */
        $roleEntity = $this->getRoleEntityById($roleId, $ownershipContext);

        return $this->deny($roleEntity, $subjectId);
    }

    /**
     * @internal
     * @param int $roleId
     * @param OwnershipContext $ownershipContext
     * @return CrudEntity
     */
    protected function getRoleEntityById(int $roleId, OwnershipContext $ownershipContext): CrudEntity
    {
        return $this->roleRepository
            ->fetchOneById($roleId, $ownershipContext);
    }
}
