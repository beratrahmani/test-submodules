<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Frontend;

use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\B2bControllerRedirectException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\OrderList\Framework\OrderListRepository;
use Shopware\B2B\OrderList\Framework\OrderListService;
use Shopware\B2B\OrderList\Framework\RemoteBoxService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OrderListRemoteController extends RemoteBoxController
{
    /**
     * @var string
     */
    private $action;

    /**
     * @var OrderListRepository
     */
    private $orderListRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var OrderListService
     */
    private $orderListService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var RemoteBoxService
     */
    private $remoteBoxService;

    /**
     * @param OrderListRepository $orderListRepository
     * @param CurrencyService $currencyService
     * @param RemoteBoxService $remoteBoxService
     * @param OrderListService $orderListService
     * @param AuthenticationService $authenticationService
     * @param GridHelper $gridHelper
     */
    public function __construct(
        OrderListRepository $orderListRepository,
        CurrencyService $currencyService,
        RemoteBoxService $remoteBoxService,
        OrderListService $orderListService,
        AuthenticationService $authenticationService,
        GridHelper $gridHelper
    ) {
        parent::__construct(
            $remoteBoxService,
            $authenticationService,
            $currencyService,
            $gridHelper,
            $orderListRepository
        );
        $this->orderListRepository = $orderListRepository;
        $this->currencyService = $currencyService;
        $this->orderListService = $orderListService;
        $this->authenticationService = $authenticationService;
        $this->remoteBoxService = $remoteBoxService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function remoteListAction(Request $request): array
    {
        $referenceNumber = $request->requireParam('referenceNumber');

        $orderLists = $this->getOrderListsFromRequest($request);

        return [
            'referenceNumber' => $referenceNumber,
            'b2b_quantity' => (int) $request->getParam('b2b_quantity'),
            'orderLists' => $orderLists,
            'orderListId' => (int) $request->getParam('orderListId'),
            'message' => $request->getParam('message'),
            'validationExceptions' => $request->getParam('validationExceptions'),
            'type' => $request->getParam('type'),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function remoteListCartAction(Request $request): array
    {
        $cartId = $request->requireParam('cartId');

        $orderLists = $this->getOrderListsFromRequest($request);

        return [
            'cartId' => $cartId,
            'orderLists' => $orderLists,
            'orderListId' => (int) $request->getParam('orderListId'),
            'message' => $request->getParam('message'),
            'validationExceptions' => $request->getParam('validationExceptions'),
            'type' => $request->getParam('type'),
        ];
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function processAddProductsToOrderListAction(Request $request)
    {
        $this->action = 'remoteList';

        $responseData = [
            'referenceNumber' => $request->getParam('referenceNumber'),
            'orderListId' => $request->requireParam('orderlist'),
        ];

        $lineItemList = $this
            ->createLineItemListFromRequest($request, $responseData);

        if ($this->getListingActionName() === 'remoteList' && count($lineItemList->references) > 0) {
            $responseData = array_merge(
                $responseData,
                ['b2b_quantity' => $lineItemList->references[0]->quantity]
            );
        }

        try {
            $this->orderListService->addListThroughLineItemList(
                (int) $request->requireParam('orderlist'),
                $lineItemList,
                $this->currencyService->createCurrencyContext(),
                $this->getOwnershipContext()
            );
        } catch (NotFoundException $e) {
            throw new B2bControllerForwardException(
                $this->getListingActionName(),
                $this->getControllerName(),
                'frontend',
                array_merge($responseData, ['message' => ['key' => 'NoOrderList', 'type' => 'error'], ])
            );
        } catch (ValidationException $e) {
            $this->remoteBoxService->addError($e);
            throw new B2bControllerForwardException(
                $this->getListingActionName(),
                $this->getControllerName(),
                'frontend',
                array_merge($responseData, $this->getMessages(false))
            );
        }

        $responseData = array_merge($responseData, $this->getMessages());

        throw new B2bControllerForwardException(
            $this->getListingActionName(),
            $this->getControllerName(),
            'frontend',
            $responseData
        );
    }

    /**
     * @param Request $request
     * @throws B2bControllerRedirectException
     */
    public function addListThroughCartAction(Request $request)
    {
        $this->action = 'remoteListCart';

        $responseData = [
            'cartId' => $request->requireParam('cartId'),
            'orderListId' => $request->requireParam('orderlist'),
        ];

        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->getOwnershipContext();

        try {
            $orderList = $this->orderListRepository
                ->fetchOneById((int) $responseData['orderListId'], $currencyContext, $ownershipContext);
        } catch (NotFoundException $e) {
            throw new B2bControllerForwardException(
                $this->getListingActionName(),
                $this->getControllerName(),
                'frontend',
                array_merge($responseData, ['message' => ['key' => 'NoOrderList', 'type' => 'error'], ])
            );
        }

        try {
            $this->orderListService->addListThroughCart(
                $orderList,
                $responseData['cartId'],
                $this->authenticationService->getIdentity()->getOwnershipContext(),
                $currencyContext
            );
        } catch (ValidationException $e) {
            $this->remoteBoxService->addError($e);
            throw new B2bControllerForwardException(
                $this->getListingActionName(),
                $this->getControllerName(),
                'frontend',
                array_merge($responseData, $this->getMessages(false))
            );
        }


        throw new B2bControllerForwardException(
            $this->getListingActionName(),
            $this->getControllerName(),
            'frontend',
            array_merge($responseData, ['message' => ['key' => 'Success', 'type' => 'success'], ])
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getControllerName(): string
    {
        return 'b2borderlistremote';
    }

    /**
     * {@inheritdoc}
     */
    protected function getListingActionName(): string
    {
        return $this->action;
    }
}
