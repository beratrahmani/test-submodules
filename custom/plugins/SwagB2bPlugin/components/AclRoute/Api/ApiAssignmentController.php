<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Api;

use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Contact\Framework\ContactEntity;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\Role\Framework\RoleEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

abstract class ApiAssignmentController
{
    /**
     * @var AclRepository
     */
    private $aclRepository;

    /**
     * @var DebtorAuthenticationIdentityLoader
     */
    private $contextIdentityLoader;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @param AclRepository $aclRepository
     * @param DebtorAuthenticationIdentityLoader $contextIdentityLoader
     * @param LoginContextService $loginContextService
     */
    public function __construct(
        AclRepository $aclRepository,
        DebtorAuthenticationIdentityLoader $contextIdentityLoader,
        LoginContextService $loginContextService
    ) {
        $this->aclRepository = $aclRepository;
        $this->contextIdentityLoader = $contextIdentityLoader;
        $this->loginContextService = $loginContextService;
    }

    /**
     * @param ContactEntity|RoleEntity $context
     * @return array
     */
    public function getAllGrant($context): array
    {
        $subjects = $this->aclRepository->fetchAllGrantableIds($context);

        return ['success' => true, 'allowed' => $subjects, 'totalCount' => count($subjects)];
    }

    /**
     * @param ContactEntity|RoleEntity $context
     * @return array
     */
    public function getAllAllowed($context)
    {
        $subjects = $this->aclRepository->getAllAllowedIds($context);

        return ['success' => true, 'allowed' => $subjects, 'totalCount' => count($subjects)];
    }

    /**
     * @param ContactEntity|RoleEntity $context
     * @param int $subjectId
     * @return array
     */
    public function getAllowed($context, int $subjectId)
    {
        $allowed = $this->aclRepository->isAllowed($context, $subjectId);
        $grantable = $this->aclRepository->isGrantable($context, $subjectId);

        return ['success' => true, 'allowed' => $allowed, 'grantable' => $grantable];
    }

    /**
     * @param ContactEntity|RoleEntity $context
     * @param int $subjectId
     * @param bool $grant
     * @return array
     */
    public function allow($context, int $subjectId, bool $grant)
    {
        $this->aclRepository->allow($context, $subjectId, $grant);

        return ['success' => true];
    }

    /**
     * @param ContactEntity|RoleEntity $context
     * @param array $subjects
     * @return array
     */
    public function multipleAllow($context, array $subjects)
    {
        $this->aclRepository->allowAll($context, array_map('intval', $subjects));

        return ['success' => true, 'allowed' => $subjects];
    }

    /**
     * @param ContactEntity|RoleEntity $context
     * @param array $subjects
     * @return array
     */
    public function multipleDeny($context, array $subjects)
    {
        $this->aclRepository->denyAll($context, array_map('intval', $subjects));

        return ['success' => true, 'denied' => $subjects];
    }

    /**
     * @param ContactEntity|RoleEntity $context
     * @param int $contingentId
     * @return array
     */
    public function deny($context, int $contingentId)
    {
        $this->aclRepository->deny($context, $contingentId);

        return ['success' => true];
    }

    /**
     * @param string $debtorEmail
     * @return OwnershipContext
     */
    protected function getDebtorOwnershipContextByEmail(string $debtorEmail): OwnershipContext
    {
        return $this->contextIdentityLoader
            ->fetchIdentityByEmail(
                $debtorEmail,
                $this->loginContextService
            )
            ->getOwnershipContext();
    }
}
