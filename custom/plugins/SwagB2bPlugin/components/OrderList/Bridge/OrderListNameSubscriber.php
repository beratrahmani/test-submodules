<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Bridge;

use Enlight\Event\SubscriberInterface;

use Shopware\B2B\Cart\Bridge\CartAccessSubscriber;
use Shopware\B2B\Order\Framework\OrderContext;

class OrderListNameSubscriber implements SubscriberInterface
{
    /**
     * @var OrderListRelationRepository
     */
    private $orderListRelationRepository;

    /**
     * @param OrderListRelationRepository $orderListRelationRepository
     */
    public function __construct(OrderListRelationRepository $orderListRelationRepository)
    {
        $this->orderListRelationRepository = $orderListRelationRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CartAccessSubscriber::EVENT_NAME => 'addOrderListReferences',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function addOrderListReferences(\Enlight_Event_EventArgs $args)
    {
        /** @var OrderContext $orderContext */
        $orderContext = $args->get('orderContext');
        $basket = Shopware()->Modules()->Order()->sBasketData;

        foreach ($basket['content'] as $item) {
            if (!$item['b2b_order_list']) {
                continue;
            }

            $productNumber = $item['ordernumber'];
            $listName = $item['b2b_order_list'];

            $this->orderListRelationRepository
                ->addOrderListNameToLineItemReference($orderContext->id, $productNumber, $listName);
        }
    }
}
