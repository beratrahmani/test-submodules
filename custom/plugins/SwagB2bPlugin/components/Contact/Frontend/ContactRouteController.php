<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\AclRoute\Framework\AclRouteAssignmentService;
use Shopware\B2B\AclRoute\Framework\AclRouteRepository;
use Shopware\B2B\AclRoute\Framework\AclRouteService;
use Shopware\B2B\AclRoute\Frontend\AssignmentController;
use Shopware\B2B\Common\Entity;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class ContactRouteController extends AssignmentController
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @param AuthenticationService $authenticationService
     * @param AclRouteRepository $aclRouteRepository
     * @param AclRepository $aclRouteAclRepository
     * @param AclAccessExtensionService $aclAccessExtensionService
     * @param AclRouteService $aclRouteService
     * @param ContactRepository $contactRepository
     * @param AclRouteAssignmentService $aclRouteAssignmentService
     * @param array $routeMapping
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AclRouteRepository $aclRouteRepository,
        AclRepository $aclRouteAclRepository,
        AclAccessExtensionService $aclAccessExtensionService,
        AclRouteService $aclRouteService,
        ContactRepository $contactRepository,
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
        $this->contactRepository = $contactRepository;
    }

    /**
     * @param int $id
     * @return Entity
     */
    protected function getContextEntity(int $id): Entity
    {
        return $this->contactRepository
            ->fetchOneById($id, $this->getOwnershipContext());
    }

    /**
     * @return string
     */
    protected function getContextParameterName(): string
    {
        return 'contactId';
    }
}
