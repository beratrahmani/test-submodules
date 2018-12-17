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

namespace SwagAboCommerce\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_View_Default;

class Backend implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Backend_Article' => 'onBackendProductPostDispatch',
            'Enlight_Controller_Action_PostDispatch_Backend_ProductStream' => 'onBackendProductStreamPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onPostDispatchBackend',
            'Enlight_Controller_Action_PostDispatch_Backend_Config' => 'addAboCommerceListingToConfigModule',
        ];
    }

    /**
     * Handles the Enlight_Controller_Action_PostDispatch_Backend_Article event.
     * Extends the view with the new AboCommerce tab.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onBackendProductPostDispatch(\Enlight_Event_EventArgs $args)
    {
        /* @var \Enlight_Controller_Action $subject */
        $subject = $args->get('subject');
        $view = $subject->View();
        $request = $subject->Request();

        //if the controller action name equals "load" we have to load all application components.
        if ($request->getActionName() === 'load') {
            $view->extendsTemplate('backend/article/view/detail/abo_commerce_window.js');
        }

        //if the controller action name equals "index" we have to extend the backend product application
        if ($request->getActionName() === 'index') {
            $view->extendsTemplate('backend/article/abo_commerce.js');
        }
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onBackendProductStreamPostDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        $controller = $args->getSubject();

        $controller->View()->extendsTemplate('backend/product_stream/view/condition_list/abo_commerce_condition_panel.js');
    }

    /**
     * Global post dispatch of the backend controller to load the menu icon
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchBackend(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $subject */
        $subject = $args->get('subject');

        /** @var Enlight_View_Default $view */
        $view = $subject->View();

        if (strtolower($subject->Request()->getControllerName()) === 'index') {
            $view->extendsTemplate('backend/abo_menu_item.tpl');
        }
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function addAboCommerceListingToConfigModule(\Enlight_Controller_ActionEventArgs $args)
    {
        $args->getSubject()->View()->extendsTemplate('backend/config/abo_commerce_extension.js');
    }
}
