<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoginHeaderSender implements SubscriberInterface
{
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
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'addLoginHeader',
        ];
    }

    public function addLoginHeader()
    {
        $response = $this->container->get('front')->Response();

        if (!$this->getAuthenticationService()->isB2b()) {
            $response->setHeader('b2b-no-login', true);
        }
    }

    /**
     * @internal
     * @return AuthenticationService
     */
    protected function getAuthenticationService(): AuthenticationService
    {
        return $this->container->get('b2b_front_auth.authentication_service');
    }
}
