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

namespace SwagProductAdvisor\Subscriber;

use Enlight\Event\SubscriberInterface;

/**
 * Class Resources
 * Registers template directory for frontend and backend advisor controllers
 */
class Resources implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDir;

    /**
     * @param string $pluginDir
     */
    public function __construct($pluginDir)
    {
        $this->pluginDir = $pluginDir;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch' => 'registerTemplates',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function registerTemplates(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Advisor $subject */
        $subject = $args->get('subject');
        $view = $subject->View();

        $view->addTemplateDir($this->pluginDir . '/Resources/views/');
    }
}
