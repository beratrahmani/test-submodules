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

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs;
use Shopware\Bundle\MediaBundle\Struct\MediaPosition;

/**
 * Class Advisor
 * Collects all main events for the advisor-functionality.
 */
class Advisor implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Collect_MediaPositions' => 'onCollectMediaPositions',
            'Enlight_Controller_Action_PostDispatch_Backend_Index' => 'registerMenuIcon',
        ];
    }

    /**
     * Collect the media-positions, so the medias being used in the advisor won't be moved to the trash-bin.
     *
     * @return ArrayCollection
     */
    public function onCollectMediaPositions()
    {
        return new ArrayCollection(
            [
                new MediaPosition('s_plugin_product_advisor_advisor', 'teaser_banner_id'),
                new MediaPosition('s_plugin_product_advisor_advisor', 'description', 'path', MediaPosition::PARSE_HTML),
                new MediaPosition('s_plugin_product_advisor_question', 'info_text', 'path', MediaPosition::PARSE_HTML),
                new MediaPosition('s_plugin_product_advisor_answer', 'media_id'),
            ]
        );
    }

    /**
     * @param Enlight_Controller_ActionEventArgs $args
     */
    public function registerMenuIcon(Enlight_Controller_ActionEventArgs $args)
    {
        $controller = $args->getSubject();
        $view = $controller->View();

        if ($view->hasTemplate()) {
            $view->extendsTemplate('backend/advisor/advisor_menu_item.tpl');
        }
    }
}
