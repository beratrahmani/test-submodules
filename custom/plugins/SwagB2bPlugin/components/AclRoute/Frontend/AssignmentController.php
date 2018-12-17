<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\AclRoute\Framework\AclRouteAssignmentService;
use Shopware\B2B\AclRoute\Framework\AclRouteEntity;
use Shopware\B2B\AclRoute\Framework\AclRouteRepository;
use Shopware\B2B\AclRoute\Framework\AclRouteService;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Entity;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

/**
 * Base implementation of role assignment controller
 */
abstract class AssignmentController
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
     * @var  AclRepository
     */
    private $aclRouteAclRepository;

    /**
     * @var AclAccessExtensionService
     */
    private $aclAccessExtensionService;

    /**
     * @var AclRouteService
     */
    private $aclRouteService;

    /**
     * @var AclRouteAssignmentService
     */
    private $aclRouteAssignmentService;

    /**
     * @var array
     */
    private $routeMapping;

    /**
     * @param AuthenticationService $authenticationService
     * @param AclRouteRepository $aclRouteRepository
     * @param AclRepository $aclRouteAclRepository
     * @param AclAccessExtensionService $aclAccessExtensionService
     * @param AclRouteService $aclRouteService
     * @param AclRouteAssignmentService $aclRouteAssignmentService
     * @param array $routeMapping
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AclRouteRepository $aclRouteRepository,
        AclRepository $aclRouteAclRepository,
        AclAccessExtensionService $aclAccessExtensionService,
        AclRouteService $aclRouteService,
        AclRouteAssignmentService $aclRouteAssignmentService,
        array $routeMapping
    ) {
        $this->aclRouteRepository = $aclRouteRepository;
        $this->aclRouteAclRepository = $aclRouteAclRepository;
        $this->aclAccessExtensionService = $aclAccessExtensionService;
        $this->aclRouteService = $aclRouteService;
        $this->authenticationService = $authenticationService;
        $this->aclRouteAssignmentService = $aclRouteAssignmentService;
        $this->routeMapping = $routeMapping;
    }

    /**
     * @param int $id
     * @return Entity
     */
    abstract protected function getContextEntity(int $id): Entity;

    /**
     * @return string
     */
    abstract protected function getContextParameterName(): string;

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $id = (int) $request->requireParam($this->getContextParameterName());

        $contextEntity = $this->getContextEntity($id);

        $ownership = $this->authenticationService->getIdentity()
            ->getOwnershipContext();

        $privilegeList = $this->aclRouteRepository
            ->fetchControllerList($ownership);

        $this->aclAccessExtensionService
            ->extendEntitiesWithAssignment($this->aclRouteAclRepository, $contextEntity, $privilegeList);

        $this->aclAccessExtensionService
            ->extendEntitiesWithIdentityOwnership($this->aclRouteAclRepository, $ownership, $privilegeList);

        $privilegeGrid = $this->transformPrivilegeListToGrid($privilegeList);

        return [
            'privilegeGrid' => $privilegeGrid,
            'actions' => $this->aclRouteService->getPrivilegeTypes(),
            $this->getContextParameterName() => $id,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function assignAction(Request $request): array
    {
        $request->checkPost('index', [$this->getContextParameterName() => $request->requireParam($this->getContextParameterName())]);

        $post = $request->getPost();

        $contextEntity = $this->getContextEntity((int) $post[$this->getContextParameterName()]);

        $ownerShipContext = $this->getOwnershipContext();

        $routes = [];
        if ($request->getParam('allow', false)) {
            $this->aclRouteAssignmentService->allow(
                $ownerShipContext,
                $contextEntity,
                (int) $post['routeId'],
                (bool) $request->getParam('grantable', false)
            );

            try {
                foreach ($this->aclRouteRepository->fetchMappedRouteIds($this->routeMapping, (int) $post['routeId']) as $routeId) {
                    $routes[] = 'allow_' . $routeId;
                }
            } catch (NotFoundException $e) {
                //nth
            }
        } else {
            $this->aclRouteAssignmentService->deny($ownerShipContext, $contextEntity, (int) $post['routeId']);
        }

        return ['body' => ['routes' => $routes]];
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function allowAllAction(Request $request)
    {
        $request->checkPost('index', [$this->getContextParameterName() => $request->requireParam($this->getContextParameterName())]);

        $post = $request->getPost();

        $contextEntity = $this->getContextEntity((int) $post[$this->getContextParameterName()]);

        $ownerShipContext = $this->getOwnershipContext();

        $this->aclRouteAssignmentService->allowAll($ownerShipContext, $contextEntity, true);

        throw new B2bControllerForwardException(
            'index',
            null,
            null,
            [$this->getContextParameterName() => $post[$this->getContextParameterName()]]
        );
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function denyAllAction(Request $request)
    {
        $request->checkPost('index', [$this->getContextParameterName() => $request->requireParam($this->getContextParameterName())]);

        $post = $request->getPost();

        $contextEntity = $this->getContextEntity((int) $post[$this->getContextParameterName()]);

        $ownerShipContext = $this->getOwnershipContext();

        $this->aclRouteAssignmentService->denyAll($ownerShipContext, $contextEntity);

        throw new B2bControllerForwardException(
            'index',
            null,
            null,
            [$this->getContextParameterName() => $post[$this->getContextParameterName()]]
        );
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function assignComponentAction(Request $request)
    {
        $request->checkPost('index', [$this->getContextParameterName() => $request->requireParam($this->getContextParameterName())]);

        $post = $request->getPost();

        $contextEntity = $this->getContextEntity((int) $post[$this->getContextParameterName()]);

        $ownerShipContext = $this->getOwnershipContext();

        $componentName = $request->requireParam('component');
        $grantable = (bool) $request->getParam('grantable');

        if ($request->getParam('allow', false)) {
            $this->aclRouteAssignmentService->allowComponent($ownerShipContext, $contextEntity, $componentName, $grantable);
        } else {
            $this->aclRouteAssignmentService->denyComponent($ownerShipContext, $contextEntity, $componentName);
        }

        throw new B2bControllerForwardException(
            'index',
            null,
            null,
            [$this->getContextParameterName() => $post[$this->getContextParameterName()]]
        );
    }

    /**
     * @internal
     * @param AclRouteEntity[] $privilegeList
     * @return array
     */
    protected function transformPrivilegeListToGrid(array $privilegeList): array
    {
        $grid = [];

        foreach ($privilegeList as $privilege) {
            if (!isset($grid[$privilege->resource_name])) {
                $grid[$privilege->resource_name] = [];
            }

            $grid[$privilege->resource_name][$privilege->privilege_type] = $privilege;
        }

        return $grid;
    }

    /**
     * @return OwnershipContext
     */
    protected function getOwnershipContext(): OwnershipContext
    {
        $ownerShipContext = $this->authenticationService->getIdentity()
            ->getOwnershipContext();

        return $ownerShipContext;
    }
}
