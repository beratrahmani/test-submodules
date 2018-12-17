<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Order\Framework\ShopOrderRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class AuthAssigner implements SubscriberInterface
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;
    /**
     * @var ShopOrderRepositoryInterface
     */
    private $shopOrderRepository;

    /**
     * @param AuthenticationService $authenticationService
     * @param ShopOrderRepositoryInterface $shopOrderRepository
     */
    public function __construct(
        AuthenticationService $authenticationService,
        ShopOrderRepositoryInterface $shopOrderRepository
    ) {
        $this->authenticationService = $authenticationService;
        $this->shopOrderRepository = $shopOrderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'sOrder::sSaveOrder::after' => 'setOrderIdentity',
        ];
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     */
    public function setOrderIdentity(\Enlight_Hook_HookArgs $args)
    {
        $orderNumber = $args->getReturn();
        $args->setReturn($orderNumber);

        if (!$this->authenticationService->isB2b()) {
            return;
        }

        $authId = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext()
            ->authId;

        $this->shopOrderRepository
            ->setOrderIdentity((string) $orderNumber, $authId);
    }
}
