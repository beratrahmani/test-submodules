<?php declare(strict_types=1);

namespace Shopware\B2B\FastOrder\Frontend;

use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\FastOrder\Framework\FastOrderService;
use Shopware\B2B\OrderList\Framework\OrderListRepository;
use Shopware\B2B\OrderList\Framework\OrderListService;
use Shopware\B2B\OrderList\Framework\RemoteBoxService;
use Shopware\B2B\OrderList\Frontend\RemoteBoxController;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class FastOrderRemoteController extends RemoteBoxController
{
    /**
     * @var FastOrderService
     */
    private $fastOrderService;

    /**
     * @var RemoteBoxService
     */
    private $remoteBoxService;

    /**
     * @var OrderListService
     */
    private $orderListService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param FastOrderService $fastOrderService
     * @param OrderListService $orderListService
     * @param RemoteBoxService $remoteBoxService
     * @param CurrencyService $currencyService
     * @param AuthenticationService $authenticationService
     * @param OrderListRepository $orderListRepository
     * @param GridHelper $gridHelper
     */
    public function __construct(
        FastOrderService $fastOrderService,
        OrderListService $orderListService,
        RemoteBoxService $remoteBoxService,
        CurrencyService $currencyService,
        AuthenticationService $authenticationService,
        OrderListRepository $orderListRepository,
        GridHelper $gridHelper
    ) {
        parent::__construct(
            $remoteBoxService,
            $authenticationService,
            $currencyService,
            $gridHelper,
            $orderListRepository
        );
        $this->fastOrderService = $fastOrderService;
        $this->remoteBoxService = $remoteBoxService;
        $this->orderListService = $orderListService;
        $this->currencyService = $currencyService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function remoteListFastOrderAction(Request $request): array
    {
        $orderLists = $this->getOrderListsFromRequest($request);

        return [
            'orderLists' => $orderLists,
            'orderListId' => (int) $request->getParam('orderListId'),
            'message' => $request->getParam('message'),
            'validationExceptions' => $request->getParam('validationExceptions'),
        ];
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function addProductsToOrderListAction(Request $request)
    {
        $responseData = [
            'orderListId' => $request->requireParam('orderlist'),
        ];
        $ownershipContext = $this->getOwnershipContext();

        $lineItemList = $this
            ->createLineItemListFromRequest($request, $responseData);

        try {
            $this->orderListService->addListThroughLineItemList(
                (int) $request->requireParam('orderlist'),
                $lineItemList,
                $this->currencyService->createCurrencyContext(),
                $ownershipContext
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
     * @throws B2bControllerForwardException
     */
    public function addProductsToCartAction(Request $request)
    {
        $responseData = [
            'orderListId' => $request->getParam('orderlist'),
        ];

        $lineItemList = $this
            ->createLineItemListFromRequest($request, $responseData);

        $this->fastOrderService->produceCart($lineItemList);

        $responseData = array_merge($responseData, $this->getMessages());

        $responseData['message']['key'] = $responseData['message']['key'] . 'Cart';

        throw new B2bControllerForwardException(
            $this->getListingActionName(),
            $this->getControllerName(),
            'frontend',
            $responseData
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getControllerName(): string
    {
        return 'b2bfastorderremote';
    }

    /**
     * {@inheritdoc}
     */
    protected function getListingActionName(): string
    {
        return 'remoteListFastOrder';
    }
}
