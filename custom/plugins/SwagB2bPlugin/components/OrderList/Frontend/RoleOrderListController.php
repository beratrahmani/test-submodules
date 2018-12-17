<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\OrderList\Framework\OrderListRepository;
use Shopware\B2B\OrderList\Framework\OrderListSearchStruct;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class RoleOrderListController
{
    /**
     * @var OrderListRepository
     */
    private $orderListRepository;

    /**
     * @var AclRepository
     */
    private $orderListAclRepository;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var AclAccessExtensionService
     */
    private $aclAccessExtensionService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param AuthenticationService $authenticationService
     * @param OrderListRepository $orderListRepository
     * @param AclRepository $orderListAclRepository
     * @param RoleRepository $roleRepository
     * @param GridHelper $gridHelper
     * @param AclAccessExtensionService $aclAccessExtensionService
     * @param CurrencyService $currencyService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        OrderListRepository $orderListRepository,
        AclRepository $orderListAclRepository,
        RoleRepository $roleRepository,
        GridHelper $gridHelper,
        AclAccessExtensionService $aclAccessExtensionService,
        CurrencyService $currencyService
    ) {
        $this->authenticationService = $authenticationService;
        $this->orderListRepository = $orderListRepository;
        $this->orderListAclRepository = $orderListAclRepository;
        $this->roleRepository = $roleRepository;
        $this->gridHelper = $gridHelper;
        $this->aclAccessExtensionService = $aclAccessExtensionService;
        $this->currencyService = $currencyService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $roleId = (int) $request->requireParam('roleId');
        $currencyContext = $this->currencyService->createCurrencyContext();

        $searchStruct = new OrderListSearchStruct();

        $this->gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        $orderLists = $this->orderListRepository
            ->fetchList($searchStruct, $ownershipContext, $currencyContext);

        $this->aclAccessExtensionService
            ->extendEntitiesWithAssignment($this->orderListAclRepository, $role, $orderLists);

        $this->aclAccessExtensionService
            ->extendEntitiesWithIdentityOwnership($this->orderListAclRepository, $ownershipContext, $orderLists);

        $count = $this->orderListRepository
            ->fetchTotalCount($searchStruct, $ownershipContext);

        $maxPage = $this->gridHelper->getMaxPage($count);
        $currentPage = $this->gridHelper->getCurrentPage($request);

        $gridState = $this->gridHelper
            ->getGridState($request, $searchStruct, $orderLists, $maxPage, $currentPage);

        return [
            'gridState' => $gridState,
            'orderLists' => $orderLists,
            'role' => $role,
        ];
    }

    /**
     * @param  Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function assignAction(Request $request)
    {
        $roleId = (int) $request->requireParam('roleId');

        $orderListId = (int) $request->requireParam('orderListId');

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        if ($request->getParam('allow')) {
            $this->orderListAclRepository
                ->allow($role, (int) $orderListId, (bool) $request->getParam('grantable'));
        } else {
            $this->orderListAclRepository->deny($role, $orderListId);
        }

        throw new B2bControllerForwardException('index', null, null, ['role' => $role]);
    }
}
