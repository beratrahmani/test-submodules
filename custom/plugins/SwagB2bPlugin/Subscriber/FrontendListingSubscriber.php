<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;

class FrontendListingSubscriber implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend_Listing' => 'handleAlternativeListingView',
            'Enlight_Controller_Action_PostDispatchSecure_Widgets' => 'handleAlternativeListingView',
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function handleAlternativeListingView(\Enlight_Controller_ActionEventArgs $args)
    {
        $view = $args->getSubject()->View();
        $b2bListingView = $args->getRequest()->get('b2bListingView');

        $view->assign('b2bListingView', $b2bListingView);
    }
}
