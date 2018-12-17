<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class AddressRepositorySubscriber implements SubscriberInterface
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
            'Shopware\Models\Customer\AddressRepository::getByUserQueryBuilder::before' => 'overloadUserId',
            'Shopware\Models\Customer\AddressRepository::getOneByUser::before' => 'overloadUserId',
        ];
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     */
    public function overloadUserId(\Enlight_Hook_HookArgs $args)
    {
        if (!$this->authenticationService->isB2b()) {
            return;
        }

        $args->set(
            'userId',
            $this->authenticationService->getIdentity()->getOrderCredentials()->orderUserId
        );
    }
}
