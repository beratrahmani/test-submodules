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
use Enlight_Event_EventArgs as EventArgs;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;

class ListingExtensionSubscriber implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            //extension for custom listing feature
            'Enlight_Controller_Action_PostDispatch_Backend_ProductStream' => 'onBackendProductStreamPostDispatch',
            'Enlight_Controller_Action_PostDispatch_Backend_Config' => 'onBackendConfig',

            //template extension for detail page and emotion
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'assignLiveShoppingFromProduct',
            'Enlight_Controller_Action_PostDispatchSecure_Widgets_SwagEmotionAdvanced' => 'assignLiveShoppingFromProduct',
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onBackendProductStreamPostDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        $args->getSubject()->View()->extendsTemplate(
            'backend/product_stream/view/condition_list/live_shopping_condition_panel.js'
        );
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onBackendConfig(\Enlight_Controller_ActionEventArgs $args)
    {
        $args->getSubject()->View()->extendsTemplate(
            'backend/config/live_shopping_extension.js'
        );
    }

    /**
     * Detail page expects the live shopping data in first level assignment
     *
     * @param EventArgs $arguments
     */
    public function assignLiveShoppingFromProduct(EventArgs $arguments)
    {
        /* @var \Enlight_Controller_Action $subject */
        $subject = $arguments->get('subject');

        if ($subject->Request()->getActionName() !== 'index') {
            return;
        }

        $product = $subject->View()->getAssign('sArticle');
        if (!array_key_exists('live_shopping', $product['attributes'])) {
            return;
        }

        /** @var Attribute $attribute */
        $attribute = $product['attributes']['live_shopping'];

        if (!empty($attribute->get('live_shopping'))) {
            $product['liveshoppingData']['valid_to_ts'] = 'hide';
            $subject->View()->assign('liveShopping', $attribute->get('live_shopping'));
        }
    }
}
