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

namespace SwagAdvancedCart\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Enlight_View_Default;

/**
 * Class Backend
 */
class Backend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginBasePath;

    /**
     * @param string $pluginBasePath
     */
    public function __construct($pluginBasePath)
    {
        $this->pluginBasePath = $pluginBasePath;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onPostDispatchBackend',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_SwagAdvancedCart' => 'onPostDispatchBackend',
        ];
    }

    /**
     * Global post dispatch of the backend controller to load the menu icon
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchBackend(Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $subject */
        $subject = $args->get('subject');

        /** @var Enlight_View_Default $view */
        $view = $subject->View();

        $view->addTemplateDir($this->pluginBasePath . '/Resources/views');

        if (strtolower($subject->Request()->getControllerName()) === 'index') {
            $view->extendsTemplate('backend/cart_menu_item.tpl');
        }
    }
}
