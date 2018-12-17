<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\AclRoute\Framework\AclRouteAssignmentService;
use Shopware\B2B\AclRoute\Framework\AclRouteRepository;
use Shopware\B2B\AclRoute\Framework\AclRouteService;
use Shopware\B2B\AclRoute\Frontend\AssignmentController;
use Shopware\B2B\Common\Entity;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class RoleRouteController extends AssignmentController
{
    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @param AuthenticationService $authenticationService
     * @param AclRouteRepository $aclRouteRepository
     * @param AclRepository $aclRouteAclRepository
     * @param AclAccessExtensionService $aclAccessExtensionService
     * @param AclRouteService $aclRouteService
     * @param RoleRepository $roleRepository
     * @param AclRouteAssignmentService $aclRouteAssignmentService
     * @param array $routeMapping
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AclRouteRepository $aclRouteRepository,
        AclRepository $aclRouteAclRepository,
        AclAccessExtensionService $aclAccessExtensionService,
        AclRouteService $aclRouteService,
        RoleRepository $roleRepository,
        AclRouteAssignmentService $aclRouteAssignmentService,
        array $routeMapping
    ) {
        parent::__construct(
            $authenticationService,
            $aclRouteRepository,
            $aclRouteAclRepository,
            $aclAccessExtensionService,
            $aclRouteService,
            $aclRouteAssignmentService,
            $routeMapping
        );
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param int $id
     * @return Entity
     */
    protected function getContextEntity(int $id): Entity
    {
        $ownershipContext = $this->getOwnershipContext();

        return $this->roleRepository
            ->fetchOneById($id, $ownershipContext);
    }

    /**
     * @return string
     */
    protected function getContextParameterName(): string
    {
        return 'roleId';
    }
}
