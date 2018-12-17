<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Framework;

use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Acl\Framework\AclRepositoryFactory;
use Shopware\B2B\Acl\Framework\AclUnsupportedContextException;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

/**
 * Main routing service contains inspection methods for routing
 */
class AclRouteService
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var AclRouteRepository
     */
    private $aclRouteRepository;

    /**
     * @var AclRepository
     */
    private $aclRepository;

    /**
     * @param AuthenticationService $authenticationService
     * @param AclRouteRepository $aclRouteRepository
     * @param AclRepositoryFactory $aclRegistry
     * @throws \Shopware\B2B\Acl\Framework\AclUnsupportedContextException
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AclRouteRepository $aclRouteRepository,
        AclRepositoryFactory $aclRegistry
    ) {
        $this->authenticationService = $authenticationService;
        $this->aclRouteRepository = $aclRouteRepository;
        $this->aclRepository = $aclRegistry
            ->createRepository(AclRouteRepository::PRIVILEGE_TABLE_NAME);
    }

    /**
     * @param string $controllerName
     * @param string $actionName
     * @return bool
     */
    public function isRouteAllowed(string $controllerName, string $actionName): bool
    {
        if (!$this->authenticationService->isB2b()) {
            return true;
        }

        try {
            $privilegeId = $this->aclRouteRepository
                ->fetchPrivilegeIdByControllerAndActionName(
                    strtolower($controllerName),
                    strtolower($actionName)
                );
        } catch (NotFoundException $e) {
            return true;
        }

        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        try {
            return $this->aclRepository
                ->isAllowed($ownershipContext, $privilegeId);
        } catch (AclUnsupportedContextException $e) {
            return true;
        }
    }

    /**
     * @return string[]
     */
    public function getPrivilegeTypes(): array
    {
        return [
            'list',
            'detail',
            'update',
            'create',
            'delete',
            'assign',
        ];
    }
}
