<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Enlight\Event\SubscriberInterface;

use Shopware\B2B\Cart\Bridge\CartAccessSubscriber;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderContextRepository;

class OrderRequestedDeliveryDateSubscriber implements SubscriberInterface
{
    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

    /**
     * @param OrderContextRepository $orderContextRepository
     */
    public function __construct(OrderContextRepository $orderContextRepository)
    {
        $this->orderContextRepository = $orderContextRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CartAccessSubscriber::EVENT_NAME => 'addOrderRequestedDeliveryDate',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function addOrderRequestedDeliveryDate(\Enlight_Event_EventArgs $args)
    {
        /** @var OrderContext $orderContext */
        $orderContext = $args->get('orderContext');
        $requestedDeliveryDate = Shopware()->Front()->Request()
            ->getParam('b2bRequestedDeliveryDate', (string) $orderContext->requestedDeliveryDate);

        if (!$requestedDeliveryDate) {
            return;
        }

        $orderContext->requestedDeliveryDate = $requestedDeliveryDate;

        $this->orderContextRepository->updateContext($orderContext);
    }
}
