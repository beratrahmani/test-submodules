<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\AclRoute\Framework\AclRouteService;

class RequestInterceptorSubscriber implements SubscriberInterface
{
    const ERROR_ACTION = 'error';
    const ERROR_SILENT_ACTION = 'silentError';
    const ERROR_CONTROLLER = 'b2bacl';

    /**
     * @var AclRouteService
     */
    private $aclRoutingService;

    /**
     * @param AclRouteService $aclRoutingService
     */
    public function __construct(
        AclRouteService $aclRoutingService
    ) {
        $this->aclRoutingService = $aclRoutingService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend' => 'redirectIfInaccessible',
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function redirectIfInaccessible(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Request_Request $request */
        $request = $args->getRequest();

        if ($request->getActionName() === self::ERROR_ACTION && $request->getControllerName() === self::ERROR_CONTROLLER) {
            return;
        }

        $allowed = $this->aclRoutingService
            ->isRouteAllowed($request->getControllerName(), $request->getActionName());

        if (!$allowed) {
            $request->setActionName(self::ERROR_ACTION);
            $request->setControllerName(self::ERROR_CONTROLLER);
            $request->setDispatched(false);
        }
    }
}
