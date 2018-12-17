<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class AdvancedCartSubscriber implements SubscriberInterface
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
    public static function getSubscribedEvents(): array
    {
        return [
            'SwagAdvancedCart\Subscriber\Basket::saved' => 'stopAdvancedCart',
            'SwagAdvancedCart\Subscriber\Basket::removed' => 'stopAdvancedCart',
            'SwagAdvancedCart\Subscriber\Basket::update' => 'stopAdvancedCart',
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     * @return bool
     */
    public function stopAdvancedCart(\Enlight_Event_EventArgs $args)
    {
        if ($this->authenticationService->isB2b() === true) {
            return true;
        }
    }
}
