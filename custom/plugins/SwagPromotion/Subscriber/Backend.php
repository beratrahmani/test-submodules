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

namespace SwagPromotion\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use SwagPromotion\Components\MetaData\FieldInfo;

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
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Analytics' => 'onPostDispatchAnalytics',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onBackendIndex',
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchAnalytics(Enlight_Event_EventArgs $args)
    {
        /* @var \Shopware_Controllers_Backend_Analytics $subject */
        $subject = $args->getSubject();
        $request = $subject->Request();
        $view = $subject->View();

        if ($request->getActionName() === 'index') {
            $view->extendsTemplate('backend/analytics/promotion_analytics.js');
        }

        if ($request->getActionName() === 'load') {
            $view->extendsTemplate('backend/analytics/store/promotion/navigation.js');
            $view->extendsTemplate('backend/analytics/store/promotion/details/navigation_details.js');
        }
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onBackendIndex(Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_Index $subject */
        $subject = $args->getSubject();
        $view = $subject->View();
        $request = $subject->Request();

        if ($request->getActionName() === 'index') {
            $view->extendsTemplate('backend/swag_rule_tree/app.js');
            $view->extendsTemplate('backend/icons.tpl');

            $fieldInfo = new FieldInfo();
            $view->assign('ruleTreeMetaData', json_encode($fieldInfo->get()));
        }
    }
}
