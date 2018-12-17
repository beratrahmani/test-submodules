<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeIdentity;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class FrontendSalesRepresentative implements SubscriberInterface
{
    /**
     * @var array
     */
    private static $whitelist = [
        'account' => ['logout'],
        'b2bsalesrepresentative' => ['__all__'],
        'checkout' => ['ajaxCart'],
        'ajax_search' => ['index'],
        'b2baccount' => ['__all__'],
    ];

    /**
     * @var AuthenticationService
     */
    private $authService;

    /**
     * @param AuthenticationService $authService
     */
    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend' => 'redirectToController',
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function redirectToController(\Enlight_Controller_ActionEventArgs $args)
    {
        if (!$this->authService->isB2b() || !($this->authService->getIdentity() instanceof SalesRepresentativeIdentity)) {
            return;
        }

        $requestedController = $args->getRequest()->getControllerName();
        $requestedAction = $args->getRequest()->getActionName();

        if (isset(self::$whitelist[$requestedController])
            && (in_array($requestedAction, self::$whitelist[$requestedController], true)
            || in_array('__all__', self::$whitelist[$requestedController], true))
        ) {
            return;
        }

        $args->getSubject()->redirect([
            'controller' => 'b2bsalesrepresentative',
            'action' => 'index',
        ]);
    }
}
