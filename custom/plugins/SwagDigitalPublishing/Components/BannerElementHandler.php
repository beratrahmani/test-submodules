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

namespace SwagDigitalPublishing\Components;

use Shopware\Bundle\EmotionBundle\ComponentHandler\ComponentHandlerInterface;
use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagDigitalPublishing\Services\ContentBannerInterface;

class BannerElementHandler implements ComponentHandlerInterface
{
    const NAME = 'emotion-digital-publishing';

    /**
     * @var ContentBannerInterface
     */
    private $contentBannerService;

    /**
     * @param ContentBannerInterface $contentBannerService
     */
    public function __construct(ContentBannerInterface $contentBannerService)
    {
        $this->contentBannerService = $contentBannerService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Element $element)
    {
        return $element->getComponent()->getType() === self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $data = array_merge($element->getConfig()->getAll(), $element->getData()->getAll());

        $element->getData()->set(
            'contentBanner',
            $this->contentBannerService->get(
                $data['digital_publishing_banner_id'],
                $context
            )
        );
    }
}
