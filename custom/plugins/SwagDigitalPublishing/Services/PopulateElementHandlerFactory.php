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

namespace SwagDigitalPublishing\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight_Event_EventManager as EventManager;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use SwagDigitalPublishing\Components\ElementHandler\ButtonHandler;
use SwagDigitalPublishing\Components\ElementHandler\ImageHandler;
use SwagDigitalPublishing\Components\ElementHandler\PopulateElementHandlerInterface;
use SwagDigitalPublishing\Components\ElementHandler\TextHandler;

class PopulateElementHandlerFactory implements PopulateElementHandlerFactoryInterface
{
    /**
     * @var ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var PopulateElementHandlerInterface[]
     */
    private $handler;
    /**
     * @var LegacyStructConverter
     */
    private $legacyStructConverter;

    /**
     * PopulateElementHandlerFactory constructor.
     *
     * @param EventManager                $eventManager
     * @param ListProductServiceInterface $listProductService
     * @param MediaServiceInterface       $mediaService
     * @param LegacyStructConverter       $legacyStructConverter
     */
    public function __construct(
        EventManager $eventManager,
        ListProductServiceInterface $listProductService,
        MediaServiceInterface $mediaService,
        LegacyStructConverter $legacyStructConverter
    ) {
        $this->listProductService = $listProductService;
        $this->mediaService = $mediaService;
        $this->legacyStructConverter = $legacyStructConverter;

        $this->createHandler();

        $newHandler = new ArrayCollection();
        $eventManager->collect('Shopware_DigitalPublishing_Collect_ElementHandler', $newHandler);
        $this->handler = array_merge($newHandler->toArray(), $this->handler);
    }

    /**
     * {@inheritdoc}
     */
    public function getHandler(array $element)
    {
        /** @var PopulateElementHandlerInterface $handler */
        foreach ($this->handler as $handler) {
            if ($handler->canHandle($element)) {
                return $handler;
            }
        }

        throw new \RuntimeException(sprintf('Handler for element %s not found', $element['name']));
    }

    /**
     * Creates known element handler
     */
    private function createHandler()
    {
        $this->handler = [
            new ButtonHandler($this->listProductService, $this->legacyStructConverter),
            new ImageHandler($this->mediaService, $this->legacyStructConverter),
            new TextHandler(),
        ];
    }
}
