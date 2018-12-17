<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Order\Bridge\OrderChangeTrigger;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceRepositoryInterface;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OrderAuditLogSubscriber implements SubscriberInterface
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OrderClearanceService
     */
    private $orderClearanceService;

    /**
     * @param AuthenticationService $authenticationService
     * @param OrderClearanceService $orderClearanceService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        OrderClearanceService $orderClearanceService
    ) {
        $this->authenticationService = $authenticationService;
        $this->orderClearanceService = $orderClearanceService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderChangeTrigger::EVENT_NAME => 'writeLogEntry',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function writeLogEntry(\Enlight_Event_EventArgs $args)
    {
        $orderContext = $args->get('orderContext');

        if (!$this->authenticationService->isB2b()) {
            return;
        }

        if (OrderClearanceRepositoryInterface::STATUS_ORDER_OPEN !== $args->get('newStatus')) {
            return;
        }

        if (OrderClearanceRepositoryInterface::STATUS_ORDER_CLEARANCE !== $args->get('oldStatus')) {
            return;
        }

        $this->orderClearanceService
            ->createOrderAcceptedStatusChangeLogEntry($orderContext->id, $this->authenticationService->getIdentity());
    }
}
