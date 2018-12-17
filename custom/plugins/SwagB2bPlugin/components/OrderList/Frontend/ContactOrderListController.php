<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\OrderList\Framework\OrderListRepository;
use Shopware\B2B\OrderList\Framework\OrderListSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class ContactOrderListController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OrderListRepository
     */
    private $orderListRepository;

    /**
     * @var AclRepository
     */
    private $orderListAclRepository;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var AclAccessExtensionService
     */
    private $aclAccessExtensionService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param AuthenticationService $authenticationService
     * @param OrderListRepository $orderListRepository
     * @param AclRepository $orderListAclRepository
     * @param ContactRepository $contactRepository
     * @param GridHelper $gridHelper
     * @param AclAccessExtensionService $aclAccessExtensionService
     * @param CurrencyService $currencyService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        OrderListRepository $orderListRepository,
        AclRepository $orderListAclRepository,
        ContactRepository $contactRepository,
        GridHelper $gridHelper,
        AclAccessExtensionService $aclAccessExtensionService,
        CurrencyService $currencyService
    ) {
        $this->authenticationService = $authenticationService;
        $this->orderListRepository = $orderListRepository;
        $this->orderListAclRepository = $orderListAclRepository;
        $this->contactRepository = $contactRepository;
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
        $contactId = (int) $request->requireParam('contactId');
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $contact = $this->contactRepository
            ->fetchOneById($contactId, $ownershipContext);

        $searchStruct = new OrderListSearchStruct();
        $this->gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        $orderLists = $this->orderListRepository
            ->fetchList($searchStruct, $ownershipContext, $currencyContext);

        $this->aclAccessExtensionService
            ->extendEntitiesWithAssignment($this->orderListAclRepository, $contact, $orderLists);

        $this->aclAccessExtensionService
            ->extendEntitiesWithIdentityOwnership($this->orderListAclRepository, $ownershipContext, $orderLists);

        $count = $this->orderListRepository
            ->fetchTotalCount($searchStruct, $ownershipContext);

        $maxPage = $this->gridHelper->getMaxPage($count);
        $currentPage = $this->gridHelper->getCurrentPage($request);

        return [
            'gridState' => $this->gridHelper
                ->getGridState($request, $searchStruct, $orderLists, $maxPage, $currentPage),
            'orderLists' => $orderLists,
            'contact' => $contact,
        ];
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function assignAction(Request $request)
    {
        $contactId = (int) $request->requireParam('contactId');

        $orderListId = (int) $request->requireParam('orderListId');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $contact = $this->contactRepository
            ->fetchOneById($contactId, $ownershipContext);

        if ($request->getParam('allow', false)) {
            $this->orderListAclRepository
                ->allow($contact, $orderListId, (bool) $request->getParam('grantable', false));
        } else {
            $this->orderListAclRepository->deny($contact, $orderListId);
        }

        throw new B2bControllerForwardException(
            'index',
            null,
            null,
            ['contactId' => $contactId]
        );
    }
}
