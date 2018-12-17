<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;

class FrontendSearchSubscriber implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Search' => 'handleAlternativeListingView',
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function handleAlternativeListingView(\Enlight_Controller_ActionEventArgs $args)
    {
        $view = $args->getSubject()->View();
        $view->assign('b2bListingView', $args->getRequest()->get('b2bListingView'));
    }
}
