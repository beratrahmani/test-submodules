<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;

class FrontendCheckoutSubscriber implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'addSessionIdToCheckout',
            'Shopware_Modules_Basket_GetBasket_FilterSQL' => 'addOrderInfoAttributeToCartItem',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function addSessionIdToCheckout(\Enlight_Event_EventArgs $args)
    {
        $view = $args->getSubject()->View();
        $view->sessionId = Shopware()->Session()->get('sessionId');
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return string
     */
    public function addOrderInfoAttributeToCartItem(\Enlight_Event_EventArgs $args): string
    {
        $sql = $args->getReturn();
        $sql = str_replace('ob_attr6', 'ob_attr6, s_order_basket_attributes.b2b_order_list as b2b_order_list', $sql);
        $args->setReturn($sql);

        return $sql;
    }
}
