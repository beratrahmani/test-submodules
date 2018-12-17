<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Order\Framework\OrderRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class DebtorAssigner implements SubscriberInterface
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OrderRepository
     */
    private $shopOrderRepository;

    /**
     * @param AuthenticationService $authenticationService
     * @param ShopOrderRepository $shopOrderRepository
     */
    public function __construct(
        AuthenticationService $authenticationService,
        ShopOrderRepository $shopOrderRepository
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
            'sOrder::sSaveOrder::after' => 'assignDebtorToOrder',
        ];
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     */
    public function assignDebtorToOrder(\Enlight_Hook_HookArgs $args)
    {
        $orderNumber = $args->getReturn();
        $args->setReturn($orderNumber);

        if (!$this->authenticationService->isB2b()) {
            return;
        }

        $debtorId = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext()
            ->shopOwnerUserId;

        $this->shopOrderRepository->setOrderToShopOwnerUser((string) $orderNumber, $debtorId);
    }
}
