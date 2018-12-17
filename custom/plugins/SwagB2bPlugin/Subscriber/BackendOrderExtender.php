<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;

class BackendOrderExtender implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Backend_Order' => 'extendOrderModule',
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function extendOrderModule(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Request_Request $request */
        $request = $args->getSubject()->Request();

        /** @var \Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        // register templates
        $view->addTemplateDir(__DIR__ . '/../Resources/extendedViews');

        if ($request->getActionName() === 'load') {
            $view->extendsTemplate('backend/order/Reference/controller/detail.js');
            $view->extendsTemplate('backend/order/Reference/view/detail/overview.js');
        }
    }
}
