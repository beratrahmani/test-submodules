<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Enlight\Event\SubscriberInterface;

use Shopware\B2B\Cart\Bridge\CartAccessSubscriber;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderContextRepository;

class OrderReferenceSubscriber implements SubscriberInterface
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
            CartAccessSubscriber::EVENT_NAME => 'addOrderReferenceNumberToOrderContext',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function addOrderReferenceNumberToOrderContext(\Enlight_Event_EventArgs $args)
    {
        /** @var OrderContext $orderContext */
        $orderContext = $args->get('orderContext');
        $orderReference = Shopware()->Front()->Request()
            ->getParam('b2bOrderReference', (string) $orderContext->orderReference);

        if (!$orderReference) {
            return;
        }

        $orderContext->orderReference = $orderReference;

        $this->orderContextRepository->updateContext($orderContext);
    }
}
