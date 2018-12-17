<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FrontendAccountFirewall implements SubscriberInterface
{
    /**
     * @internal
     * @var array
     */
    protected static $routes = [
        'account' => [
            'ignore' => ['logout'],
            'redirectTo' => 'b2bdashboard',
        ],
        'address' => [
            'ignore' => [],
            'redirectTo' => 'b2bdashboard',
        ],
        'note' => [
            'ignore' => [],
            'redirectTo' => 'b2borderlist',
        ],
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend_Account' => 'redirectToController',
            'Enlight_Controller_Action_PreDispatch_Frontend_Address' => 'redirectToController',
            'Enlight_Controller_Action_PreDispatch_Frontend_Note' => 'redirectToController',
        ];
    }

    /**
     * @TODO refactor
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function redirectToController(\Enlight_Controller_ActionEventArgs $args)
    {
        $authService = $this->container->get('b2b_front_auth.authentication_service');
        $requestedController = $args->getRequest()->getControllerName();
        $requestedAction = $args->getRequest()->getActionName();

        if (!$authService->isB2b()) {
            return;
        }

        $routingSettings = self::$routes[$requestedController];
        $actionsToIgnore = $routingSettings['ignore'];

        if (in_array($requestedAction, $actionsToIgnore, true)) {
            return;
        }

        $redirectToController = $routingSettings['redirectTo'];

        $aclRouteService = $this->container->get('b2b_acl_route.service');

        if ($redirectToController === 'b2borderlist'
            && !$aclRouteService->isRouteAllowed($redirectToController, 'index')) {
            return;
        }

        $args->getSubject()->redirect([
            'controller' => $redirectToController,
            'action' => 'index',
        ]);
    }
}
