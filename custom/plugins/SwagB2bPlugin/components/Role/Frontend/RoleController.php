<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Frontend;

use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Role\Framework\RoleCrudService;
use Shopware\B2B\Role\Framework\RoleEntity;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\Role\Framework\RoleService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class RoleController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var RoleCrudService
     */
    private $roleCrudService;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var RoleService
     */
    private $roleService;

    /**
     * @param AuthenticationService $authenticationService
     * @param RoleRepository $roleRepository
     * @param RoleCrudService $roleCrudService
     * @param GridHelper $gridHelper
     * @param RoleService $roleService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        RoleRepository $roleRepository,
        RoleCrudService $roleCrudService,
        GridHelper $gridHelper,
        RoleService $roleService
    ) {
        $this->authenticationService = $authenticationService;
        $this->roleRepository = $roleRepository;
        $this->roleCrudService = $roleCrudService;
        $this->gridHelper = $gridHelper;
        $this->roleService = $roleService;
    }

    /**
     * @return array
     */
    public function indexAction(): array
    {
        $context = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $root = $this->roleRepository
            ->fetchRoot($context);

        return [
            'root' => $root,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function childrenAction(Request $request): array
    {
        $parentId = (int) $request->requireParam('parentId');
        $ownershipContext = $this->getOwnershipContext();

        $roles = $this->roleRepository
            ->fetchChildren($parentId, $ownershipContext);

        return [
            'nodes' => $roles,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function subtreeAction(Request $request): array
    {
        $ownershipContext = $this->getOwnershipContext();

        $openNodes = (array) $request->requireParam('openNodes');
        $roles = $this->roleService->createSubtree($openNodes, $ownershipContext);

        $root = [array_shift($roles)];

        /** @var RoleEntity $role */
        foreach ($roles as $role) {
            $root = $this->roleService->mapToTree($root, $role);
        }

        return array_merge(
            ['rootNode' => array_pop($root), ],
            $this->gridHelper->getValidationResponse('role')
        );
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function moveAction(Request $request)
    {
        $request->checkPost();

        $postData = $request->getPost();
        $roleId = (int) $request->requireParam('roleId');
        $ownershipContext = $this->getOwnershipContext();

        $crudRequest = $this->roleCrudService
            ->createMoveRecordRequest($postData);

        $this->roleCrudService->move($crudRequest, $ownershipContext);

        $parent = $this->roleRepository
            ->fetchParentByChildId($roleId, $ownershipContext);

        throw new B2bControllerForwardException('children', null, null, ['parentId' => $parent->id]);
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function removeAction(Request $request)
    {
        $request->checkPost();
        $ownershipContext = $this->getOwnershipContext();
        $postData = $request->getPost();
        $crudRequest = $this->roleCrudService->createExistingRecordRequest($postData);

        try {
            $this->roleCrudService->remove($crudRequest, $ownershipContext);
        } catch (ValidationException $validationException) {
            $this->gridHelper->pushValidationException($validationException);
        }

        $openNodes = (array) $request->requireParam('openNodes');
        throw new B2bControllerForwardException('subtree', null, null, ['openNodes' => $openNodes]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function newAction(Request $request): array
    {
        return array_merge(
            ['parentId' => $request->requireParam('parentId')],
            $this->gridHelper->getValidationResponse('role')
        );
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function createAction(Request $request)
    {
        $request->checkPost();

        $post = $request->getPost();

        $ownershipContext = $this->getOwnershipContext();

        $serviceRequest = $this->roleCrudService
            ->createNewRecordRequest($post);

        try {
            $role = $this->roleCrudService
                ->create($serviceRequest, $ownershipContext);
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);

            throw new B2bControllerForwardException('new');
        }

        throw new B2bControllerForwardException('detail', null, null, ['id' => $role->id]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function detailAction(Request $request): array
    {
        $id = (int) $request->requireParam('id');
        $ownershipContext = $this->getOwnershipContext();

        return ['role' => $this->roleRepository->fetchOneById($id, $ownershipContext)];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function editAction(Request $request): array
    {
        $id = (int) $request->requireParam('id');
        $ownershipContext = $this->getOwnershipContext();

        $validationData = $this->gridHelper->getValidationResponse('role');

        return array_merge([
            'role' => $this->roleRepository->fetchOneById($id, $ownershipContext),
        ], $validationData);
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function updateAction(Request $request)
    {
        $post = $request->getPost();

        $ownershipContext = $this->getOwnershipContext();
        $serviceRequest = $this->roleCrudService->createExistingRecordRequest($post);

        try {
            $this->roleCrudService->update($serviceRequest, $ownershipContext);
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);
        }

        throw new B2bControllerForwardException('edit', null, null, ['id' => $post['id']]);
    }

    /**
     * @internal
     * @return OwnershipContext
     */
    protected function getOwnershipContext(): OwnershipContext
    {
        return $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();
    }
}
