<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Api;

use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\Order\Framework\OrderLineItemReferenceCrudService;
use Shopware\B2B\OrderList\Framework\OrderListCrudService;
use Shopware\B2B\OrderList\Framework\OrderListRepository;
use Shopware\B2B\OrderList\Framework\OrderListSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthStorageAdapterInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderListController
{
    /**
     * @var GridHelper
     */
    private $requestHelper;

    /**
     * @var DebtorAuthenticationIdentityLoader
     */
    private $debtorRepository;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var OrderListRepository
     */
    private $orderListRepository;

    /**
     * @var OrderListCrudService
     */
    private $orderListCrudService;

    /**
     * @var OrderLineItemReferenceCrudService
     */
    private $lineItemReferenceCrudService;

    /**
     * @var LineItemListRepository
     */
    private $lineItemListRepository;

    /**
     * @var AuthStorageAdapterInterface
     */
    private $authStorageAdapter;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param OrderListRepository $orderListRepository
     * @param GridHelper $requestHelper
     * @param OrderListCrudService $orderListCrudService
     * @param DebtorAuthenticationIdentityLoader $debtorRepository
     * @param LoginContextService $loginContextService
     * @param OrderLineItemReferenceCrudService $lineItemReferenceCrudService
     * @param LineItemListRepository $lineItemListRepository
     * @param AuthStorageAdapterInterface $authStorageAdapter
     * @param CurrencyService $currencyService
     */
    public function __construct(
        OrderListRepository $orderListRepository,
        GridHelper $requestHelper,
        OrderListCrudService $orderListCrudService,
        DebtorAuthenticationIdentityLoader $debtorRepository,
        LoginContextService $loginContextService,
        OrderLineItemReferenceCrudService $lineItemReferenceCrudService,
        LineItemListRepository $lineItemListRepository,
        AuthStorageAdapterInterface $authStorageAdapter,
        CurrencyService $currencyService
    ) {
        $this->requestHelper = $requestHelper;
        $this->debtorRepository = $debtorRepository;
        $this->loginContextService = $loginContextService;
        $this->orderListRepository = $orderListRepository;
        $this->orderListCrudService = $orderListCrudService;
        $this->lineItemReferenceCrudService = $lineItemReferenceCrudService;
        $this->lineItemListRepository = $lineItemListRepository;
        $this->authStorageAdapter = $authStorageAdapter;
        $this->currencyService = $currencyService;
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function getListAction(
        string $debtorEmail,
        Request $request
    ): array {
        $search = new OrderListSearchStruct();
        $currencyContext = $this->currencyService->createCurrencyContext();

        $this->requestHelper
            ->extractSearchDataInRestApi($request, $search);

        $ownerShipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $rules = $this->orderListRepository->fetchList($search, $ownerShipContext, $currencyContext);

        $totalCount = $this->orderListRepository
            ->fetchTotalCount($search, $ownerShipContext);

        return ['success' => true, 'orderLists' => $rules, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param int $orderListId
     * @return array
     */
    public function getAction(string $debtorEmail, int $orderListId): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $orderList = $this->orderListRepository
            ->fetchOneById($orderListId, $currencyContext, $ownershipContext);

        return ['success' => true, 'orderList' => $orderList];
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function createAction(string $debtorEmail, Request $request): array
    {
        $ownerShipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $data = $request->getPost();

        $newRecord = $this->orderListCrudService
            ->createNewRecordRequest($data);

        $orderList = $this->orderListCrudService
            ->create($newRecord, $ownerShipContext);

        return ['success' => true, 'orderList' => $orderList];
    }

    /**
     * @param string $debtorEmail
     * @param int $orderListId
     * @param Request $request
     * @return array
     */
    public function updateAction(string $debtorEmail, int $orderListId, Request $request): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $data = $request->getPost();
        $data['id'] = $orderListId;

        $existingRecord = $this->orderListCrudService
            ->createExistingRecordRequest($data);

        $orderList = $this->orderListCrudService
            ->update($existingRecord, $currencyContext, $ownershipContext);

        return ['success' => true, 'orderList' => $orderList];
    }

    /**
     * @param string $debtorEmail
     * @param int $orderListId
     * @return array
     */
    public function removeAction(string $debtorEmail, int $orderListId): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $existingRecord = $this->orderListCrudService
            ->createExistingRecordRequest([
                'id' => $orderListId,
            ]);

        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $orderList = $this->orderListCrudService
            ->remove($existingRecord, $currencyContext, $ownershipContext);

        return ['success' => true, 'orderList' => $orderList];
    }

    /**
     * @param string $debtorEmail
     * @param int $orderListId
     * @return array
     */
    public function duplicateAction(string $debtorEmail, int $orderListId): array
    {
        $ownerShipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $currencyContext = $this->currencyService->createCurrencyContext();

        $orderList = $this->orderListCrudService
            ->duplicate($orderListId, $ownerShipContext, $currencyContext);

        return ['success' => true, 'orderList' => $orderList];
    }

    /**
     * @param string $debtorEmail
     * @param int $orderListId
     * @param Request $request
     * @return array
     */
    public function addItemsAction(string $debtorEmail, int $orderListId, Request $request): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $identity = $this->getDebtorIdentityByEmail($debtorEmail);

        $orderList = $this->orderListRepository->fetchOneById($orderListId, $currencyContext, $identity->getOwnershipContext());
        $items = $request->getPost();

        $this->authStorageAdapter->setIdentity($identity);

        $references = [];
        foreach ($items as $item) {
            $crudService =$this->lineItemReferenceCrudService->createCreateCrudRequest($item);
            $references[] = $this->lineItemReferenceCrudService->addLineItem($orderList->listId, $crudService, $currencyContext, $identity->getOwnershipContext());
        }

        $lineItemList = $this->lineItemListRepository->fetchOneListById($orderList->listId, $currencyContext, $identity->getOwnershipContext());

        return ['success' => true, 'orderList' => $orderList, 'lineItemList' => $lineItemList, 'items' => $references];
    }

    /**
     * @param string $debtorEmail
     * @param int $orderListId
     * @param Request $request
     * @return array
     */
    public function removeItemsAction(string $debtorEmail, int $orderListId, Request $request): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $identity = $this->getDebtorIdentityByEmail($debtorEmail);

        $orderList = $this->orderListRepository->fetchOneById($orderListId, $currencyContext, $identity->getOwnershipContext());
        $itemIds = $request->getPost();

        $this->authStorageAdapter->setIdentity($identity);

        $items = $this->lineItemListRepository
            ->fetchOneListById($orderList->listId, $currencyContext, $identity->getOwnershipContext())
            ->references;

        $references = [];
        foreach ($items as $item) {
            if (in_array($item->id, $itemIds, true)) {
                $item->id = null;
                $references[] = $item;
            }
        }

        foreach ($itemIds as $itemId) {
            $this->lineItemReferenceCrudService->deleteLineItem($orderList->listId, (int) $itemId, $currencyContext, $identity->getOwnershipContext());
        }

        $lineItemList = $this->lineItemListRepository->fetchOneListById($orderList->listId, $currencyContext, $identity->getOwnershipContext());

        return ['success' => true, 'orderList' => $orderList, 'lineItemList' => $lineItemList, 'items' => $references];
    }

    /**
     * @param string $debtorEmail
     * @param int $orderListId
     * @param Request $request
     * @return array
     */
    public function updateItemsAction(string $debtorEmail, int $orderListId, Request $request): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $identity = $this->getDebtorIdentityByEmail($debtorEmail);

        $orderList = $this->orderListRepository->fetchOneById($orderListId, $currencyContext, $identity->getOwnershipContext());
        $items = $request->getPost();

        $this->authStorageAdapter->setIdentity($identity);

        $references = [];
        foreach ($items as $item) {
            $crudService =$this->lineItemReferenceCrudService->createUpdateCrudRequest($item);
            $references[] = $this->lineItemReferenceCrudService
                ->updateLineItem($orderList->listId, $crudService, $currencyContext, $identity->getOwnershipContext());
        }

        $lineItemList = $this->lineItemListRepository->fetchOneListById($orderList->listId, $currencyContext, $identity->getOwnershipContext());

        return ['success' => true, 'orderList' => $orderList, 'lineItemList' => $lineItemList, 'items' => $references];
    }

    /**
     * @param string $debtorEmail
     * @param int $orderListId
     * @return array
     */
    public function getItemsAction(string $debtorEmail, int $orderListId): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $orderList = $this->orderListRepository->fetchOneById($orderListId, $currencyContext, $ownershipContext);

        $lineItemList = $this->lineItemListRepository->fetchOneListById($orderList->listId, $currencyContext, $ownershipContext);

        return ['success' => true, 'orderList' => $orderList, 'lineItemList' => $lineItemList, 'items' => $lineItemList->references];
    }

    /**
     * @internal
     * @param string $debtorEmail
     * @return OwnershipContext
     */
    protected function getDebtorOwnershipContextByEmail(string $debtorEmail): OwnershipContext
    {
        return $this->getDebtorIdentityByEmail($debtorEmail)->getOwnershipContext();
    }

    /**
     * @internal
     * @param string $debtorEmail
     * @return Identity
     */
    protected function getDebtorIdentityByEmail(string $debtorEmail): Identity
    {
        return $this->debtorRepository
            ->fetchIdentityByEmail(
                $debtorEmail,
                $this->loginContextService,
                true
            );
    }
}
