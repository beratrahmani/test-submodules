<?php declare(strict_types=1);

namespace Shopware\B2B\OrderOrderList\Frontend;

use Shopware\B2B\Common\Controller\B2bControllerRedirectException;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\OrderOrderList\Framework\OrderOrderListService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OrderOrderListController
{
    /**
     * @var OrderOrderListService
     */
    private $OrderOrderListService;

    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

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
     * @param OrderContextRepository $orderContextRepository
     * @param OrderOrderListService $OrderOrderListService
     * @param CurrencyService $currencyService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        OrderContextRepository $orderContextRepository,
        OrderOrderListService $OrderOrderListService,
        CurrencyService $currencyService
    ) {
        $this->authenticationService = $authenticationService;
        $this->orderContextRepository = $orderContextRepository;
        $this->OrderOrderListService = $OrderOrderListService;
        $this->currencyService = $currencyService;
    }

    /**
     * @param Request $request
     */
    public function createNewOrderListAction(Request $request)
    {
        $orderContextId = (int) $request->requireParam('id');

        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $orderContext = $this->orderContextRepository
            ->fetchOneOrderContextById($orderContextId, $ownershipContext);

        $orderList = $this->OrderOrderListService
            ->createOrderListFromOrderContext($orderContext, $ownershipContext, $currencyContext);

        throw new B2bControllerRedirectException('detail', 'b2borderlist', null, ['orderlist' => $orderList->id]);
    }
}
