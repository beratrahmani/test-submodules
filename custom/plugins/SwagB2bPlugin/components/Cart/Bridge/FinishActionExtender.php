<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Common\MvcExtension\RoutingInterceptor;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class FinishActionExtender implements SubscriberInterface
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param AuthenticationService $authenticationService
     */
    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_Frontend_Checkout_Finish' => 'listen',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return bool|void
     */
    public function listen(\Enlight_Event_EventArgs $args)
    {
        if (!$this->authenticationService->isB2b()) {
            return;
        }

        $args->setReturn(true);

        /** @var \Shopware_Controllers_Frontend_Checkout $controller */
        $controller = $args->getSubject();

        (new RoutingInterceptor())->interceptException($controller, function () use ($controller) {
            $controller->finishAction();
        });

        return true;
    }
}
