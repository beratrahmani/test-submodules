<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\Components\Random;

class OrderChangeTrigger implements SubscriberInterface
{
    const EVENT_NAME = __CLASS__ . '::change';

    /**
     * @var OrderChangeQueueRepository
     */
    private $orderChangeQueueRepository;

    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

    /**
     * @var ShopOrderRepository
     */
    private $shopOrderRepository;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @param OrderChangeQueueRepository $orderChangeQueueRepository
     * @param ShopOrderRepository $shopOrderRepository
     * @param OrderContextRepository $orderContextRepository
     * @param \Enlight_Event_EventManager $eventManager
     */
    public function __construct(
        OrderChangeQueueRepository $orderChangeQueueRepository,
        ShopOrderRepository $shopOrderRepository,
        OrderContextRepository $orderContextRepository,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->orderChangeQueueRepository = $orderChangeQueueRepository;
        $this->shopOrderRepository = $shopOrderRepository;
        $this->orderContextRepository = $orderContextRepository;

        $this->uuid = Random::getAlphanumericString(40);
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Front_StartDispatch' => 'setRequestUid',
            'Enlight_Controller_Action_PostDispatch' => 'triggerOrderChangeListener',
        ];
    }

    public function setRequestUid()
    {
        $this->orderChangeQueueRepository
            ->setRequestUid($this->uuid);
    }

    public function triggerOrderChangeListener()
    {
        foreach ($this->orderChangeQueueRepository->fetchAndClearQueueForUuid() as $orderId) {
            $this->updateOrderContextFromOrder($orderId);
        }
    }

    /**
     * @param int $orderId
     */
    public function updateOrderContextFromOrder(int $orderId)
    {
        $order = $this->shopOrderRepository->fetchOrderById($orderId);

        try {
            $orderContext = $this->orderContextRepository
                ->fetchOneOrderContextByOrderNumber($order['ordernumber']);
        } catch (NotFoundException $e) {
            return;
        }

        $newStatus = (int) $order['status'];
        $oldStatus = $orderContext->statusId;

        if ($orderContext->statusId === $newStatus) {
            return;
        }

        $orderContext->statusId = $newStatus;

        $this->orderContextRepository
            ->updateContext($orderContext);

        $this->eventManager->notify(
            self::EVENT_NAME,
            new \Enlight_Event_EventArgs([
                'oldStatus' => $oldStatus,
                'newStatus' => $newStatus,
                'orderContext' => $orderContext,
            ])
        );
    }
}
