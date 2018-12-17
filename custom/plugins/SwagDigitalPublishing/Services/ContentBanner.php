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

use Doctrine\ORM\EntityManager;
use Enlight_Event_EventManager as EventManager;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use SwagDigitalPublishing\Models\ContentBanner as ContentBannerModel;
use SwagDigitalPublishing\Models\Repository;

/**
 * Class ContentBanner
 */
class ContentBanner implements ContentBannerInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var TranslationServiceInterface
     */
    private $translationService;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @var LegacyStructConverter
     */
    private $legacyStructConverter;

    /**
     * @var PopulateElementHandlerFactoryInterface
     */
    private $handlerFactory;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @param EntityManager                          $entityManager
     * @param TranslationServiceInterface            $translationService
     * @param ListProductServiceInterface            $listProductService
     * @param MediaServiceInterface                  $mediaService
     * @param PopulateElementHandlerFactoryInterface $handlerFactory
     * @param EventManager                           $eventManager
     * @param LegacyStructConverter                  $legacyStructConverter
     */
    public function __construct(
        EntityManager $entityManager,
        TranslationServiceInterface $translationService,
        ListProductServiceInterface $listProductService,
        MediaServiceInterface $mediaService,
        PopulateElementHandlerFactoryInterface $handlerFactory,
        EventManager $eventManager,
        LegacyStructConverter $legacyStructConverter
    ) {
        $this->repository = $entityManager->getRepository(ContentBannerModel::class);
        $this->translationService = $translationService;
        $this->mediaService = $mediaService;
        $this->handlerFactory = $handlerFactory;
        $this->eventManager = $eventManager;
        $this->listProductService = $listProductService;
        $this->legacyStructConverter = $legacyStructConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id, ShopContextInterface $context)
    {
        $banner = $this->repository->getContentBannerById($id);

        return $this->populateBanner($banner, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function populateBanner(array $banner, ShopContextInterface $context)
    {
        foreach ($banner['layers'] as &$layer) {
            if (!empty($layer['link']) && strpos($layer['link'], 'http') === false) {
                $product = $this->listProductService->getList([$layer['link']], $context);
                $product = array_shift($product);

                if ($product instanceof ListProduct) {
                    $layer['product'] = $this->legacyStructConverter->convertListProductStruct($product);
                }
            }

            foreach ($layer['elements'] as &$element) {
                $element = $this->populateElement($element, $context);
            }
            unset($element);

            $layer = $this->translationService->translate($layer, $context);
        }
        unset($layer);

        if ($banner['bgType'] === 'image' && !empty($banner['mediaId'])) {
            $media = $this->mediaService->getList([$banner['mediaId']], $context);
            $media = array_shift($media);

            if ($media) {
                $banner['media'] = $this->legacyStructConverter->convertMediaStruct($media);
            }
        }

        $banner = $this->eventManager->filter(
            'SwagDigitalPublishing_ContentBanner_FilterResult',
            $banner
        );

        return $banner;
    }

    /**
     * @param array                $element
     * @param ShopContextInterface $context
     *
     * @return array
     */
    private function populateElement(array $element, ShopContextInterface $context)
    {
        $handler = $this->handlerFactory->getHandler($element);
        $payload = json_decode($element['payload'], true);

        if (!$payload) {
            $payload = [];
        }

        $element = array_merge($element, $payload);
        $element = $handler->handle($element, $context);

        return $element;
    }
}
