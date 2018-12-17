<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Frontend;

use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\OrderList\Framework\OrderListRepository;
use Shopware\B2B\OrderList\Framework\OrderListSearchStruct;
use Shopware\B2B\OrderList\Framework\RemoteBoxService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

abstract class RemoteBoxController
{
    /**
     * @var RemoteBoxService
     */
    private $remoteBoxService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var OrderListRepository
     */
    private $orderListRepository;

    /**
     * @param RemoteBoxService $remoteBoxService
     * @param AuthenticationService $authenticationService
     * @param CurrencyService $currencyService
     * @param GridHelper $gridHelper
     * @param OrderListRepository $orderListRepository
     */
    public function __construct(
        RemoteBoxService $remoteBoxService,
        AuthenticationService $authenticationService,
        CurrencyService $currencyService,
        GridHelper $gridHelper,
        OrderListRepository $orderListRepository
    ) {
        $this->remoteBoxService = $remoteBoxService;
        $this->authenticationService = $authenticationService;
        $this->currencyService = $currencyService;
        $this->gridHelper = $gridHelper;
        $this->orderListRepository = $orderListRepository;
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getOrderListsFromRequest(Request $request): array
    {
        $ownershipContext = $this->getOwnershipContext();

        $currencyContext = $this->currencyService->createCurrencyContext();

        $searchStruct = new OrderListSearchStruct();
        $searchStruct->limit = PHP_INT_MAX;

        $orderLists = $this->orderListRepository
            ->fetchList($searchStruct, $ownershipContext, $currencyContext);

        return $orderLists;
    }

    /**
     * @param bool $success
     * @return array
     */
    protected function getMessages(bool $success = true)
    {
        $messages['message'] = ['key' => 'Success', 'type' => 'success'];
        if (!$success) {
            $messages['message'] = ['key' => 'Error', 'type' => 'error'];
        }

        $errors = $this->remoteBoxService->getValidationResponse();

        if (count($errors) > 0) {
            $messages['validationExceptions'] = $errors;
        }

        return $messages;
    }

    /**
     * @param Request $request
     * @param $responseData
     * @throws B2bControllerForwardException
     * @return LineItemList
     */
    protected function createLineItemListFromRequest(Request $request, array $responseData): LineItemList
    {
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        try {
            $products = $request->requireParam('products');
        } catch (\InvalidArgumentException $e) {
            throw new B2bControllerForwardException(
                $this->getListingActionName(),
                $this->getControllerName(),
                'frontend',
                array_merge($responseData, ['message' => ['key' => 'NoProducts', 'type' => 'error'], ])
            );
        }

        try {
            $lineItemList = $this->remoteBoxService
                ->createLineItemListFromProductsRequest($products, $ownershipContext);
        } catch (\InvalidArgumentException $e) {
            throw new B2bControllerForwardException(
                $this->getListingActionName(),
                $this->getControllerName(),
                'frontend',
                array_merge($responseData, $this->getMessages(false))
            );
        }

        return $lineItemList;
    }

    /**
     * @return string
     */
    abstract protected function getControllerName(): string;

    /**
     * @return string
     */
    abstract protected function getListingActionName(): string;

    /**
     * @return OwnershipContext
     */
    protected function getOwnershipContext(): OwnershipContext
    {
        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        return $ownershipContext;
    }
}
