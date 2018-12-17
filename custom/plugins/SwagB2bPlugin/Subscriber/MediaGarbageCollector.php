<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

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
            new MediaPosition('b2b_store_front_auth', 'media_id'),
        ]);
    }
}
