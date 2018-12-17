<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\OrderList\Framework\OrderListRepository;
use Shopware\B2B\OrderList\Framework\OrderListSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\NoIdentitySetException;
use Shopware\B2B\StoreFrontAuthentication\Framework\NotAuthenticatedException;

class OrderListProvider implements SubscriberInterface
{
    /**
     * @var OrderListRepository
     */
    private $orderListRepository;

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
     * @param CurrencyService $currencyService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        OrderListRepository $orderListRepository,
        CurrencyService $currencyService
    ) {
        $this->orderListRepository = $orderListRepository;
        $this->authenticationService = $authenticationService;
        $this->currencyService = $currencyService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Search' => 'addOrderListToView',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Listing' => 'addOrderListToView',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'addOrderListToView',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'addOrderListToView',
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function addOrderListToView(\Enlight_Controller_ActionEventArgs $args)
    {
        try {
            $identity = $this->authenticationService->getIdentity();
        } catch (NotAuthenticatedException $e) {
            return;
        } catch (NoIdentitySetException $e) {
            return;
        }

        $searchStruct = new OrderListSearchStruct();

        $orderLists = $this->orderListRepository
            ->fetchList(
                $searchStruct,
                $identity->getOwnershipContext(),
                $this->currencyService->createCurrencyContext()
            );

        $view = $args->getSubject()->View();
        $view->assign('b2BorderLists', $orderLists);
    }
}
