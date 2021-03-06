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

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\MediaBundle\Struct\MediaPosition;

class MediaGarbageCollector implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Collect_MediaPositions' => 'onCollectMediaPositions',
        ];
    }

    /**
     * @return ArrayCollection
     */
    public function onCollectMediaPositions()
    {
        return new ArrayCollection([
            new MediaPosition('s_plugin_promotion', 'description', 'path', MediaPosition::PARSE_HTML),
            new MediaPosition('s_plugin_promotion', 'detail_description', 'path', MediaPosition::PARSE_HTML),
        ]);
    }
}
