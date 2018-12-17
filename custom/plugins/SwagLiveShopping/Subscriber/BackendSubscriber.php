<?php
/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagLiveShopping\Subscriber;

use Enlight\Event\SubscriberInterface;

class BackendSubscriber implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Backend_Article' => 'onBackendArticlePostDispatch',
            'Enlight_Controller_Action_PostDispatch_Backend_Index' => 'onBackendIndexPostDispatch',
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $arguments
     */
    public function onBackendArticlePostDispatch(\Enlight_Controller_ActionEventArgs $arguments)
    {
        $actionName = $arguments->getRequest()->getActionName();
        /** @var \Enlight_View_Default $view */
        $view = $arguments->get('subject')->View();

        if ($actionName === 'load') {
            $view->extendsTemplate('backend/article/view/detail/live_shopping_window.js');
        } elseif ($actionName === 'index') {
            $view->extendsTemplate('backend/article/live_shopping_app.js');
        }
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onBackendIndexPostDispatch(\Enlight_Event_EventArgs $args)
    {
        /* @var \Enlight_Controller_Action $subject */
        $subject = $args->get('subject');
        $view = $subject->View();

        $view->extendsTemplate('backend/index/liveshopping_header.tpl');
    }
}
