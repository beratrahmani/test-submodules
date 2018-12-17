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

namespace SwagDigitalPublishing\Components\Emotion\Preset\ComponentHandler;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\Api\Resource\Media as MediaResource;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Emotion\Preset\ComponentHandler\ComponentHandlerInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Media\Media;
use Shopware\Models\Shop\Shop;
use SwagDigitalPublishing\Models\ContentBanner as ContentBannerModel;

abstract class AbstractComponentHandler implements ComponentHandlerInterface
{
    /**
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * @var MediaService
     */
    protected $mediaService;

    /**
     * @var MediaResource
     */
    protected $mediaResource;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Service\Core\MediaService
     */
    protected $storeFrontMediaService;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Struct\ShopContext
     */
    protected $context;

    /**
     * @var \Shopware\Components\Compatibility\LegacyStructConverter
     */
    protected $legacyStructConverter;

    /**
     * @param ModelManager $modelManager
     * @param MediaService $mediaService
     * @param Container    $container
     */
    public function __construct(ModelManager $modelManager, MediaService $mediaService, Container $container)
    {
        $this->modelManager = $modelManager;
        $this->mediaService = $mediaService;

        $mediaResource = new MediaResource();
        $mediaResource->setContainer($container);
        $mediaResource->setManager($modelManager);

        $this->mediaResource = $mediaResource;

        $defaultShopId = $this->modelManager->getRepository(Shop::class)->getActiveDefault()->getId();
        $this->context = $container->get('shopware_storefront.context_service')->createShopContext($defaultShopId);
        $this->legacyStructConverter = $container->get('legacy_struct_converter');

        $this->storeFrontMediaService = $container->get('shopware_storefront.media_service');
    }

    /**
     * @return array
     */
    public function getLocaleMapping()
    {
        $shops = $this->modelManager->getConnection()->createQueryBuilder()
            ->select('locale.locale, shop.id, shop.name')
            ->from('s_core_shops', 'shop')
            ->leftJoin('shop', 's_core_locales', 'locale', 'locale.id = shop.locale_id')
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP);

        foreach ($shops as $key => $shopData) {
            $shops[$key] = array_combine(array_column($shopData, 'name'), array_column($shopData, 'id'));
        }

        return $shops;
    }

    /**
     * @param int $id
     *
     * @return null|Media
     */
    protected function getMediaById($id)
    {
        return $this->mediaResource->getRepository()->find($id);
    }

    /**
     * @param string $path
     *
     * @return null|Media
     */
    protected function getMediaByPath($path)
    {
        return $this->mediaResource->getRepository()->findOneBy(['path' => $path]);
    }

    /**
     * @param array      $data
     * @param Media|null $media
     *
     * @return ContentBannerModel
     */
    protected function createContentBanner(array $data, Media $media = null)
    {
        $contentBanner = new ContentBannerModel();
        $contentBanner->fromArray($data);

        if ($media) {
            $contentBanner->setMedia($media);
        }
        $this->modelManager->persist($contentBanner);
        $this->modelManager->flush($contentBanner);

        return $contentBanner;
    }

    /**
     * @param string $assetPath
     *
     * @return Media
     */
    protected function doAssetImport($assetPath)
    {
        $media = $this->mediaResource->internalCreateMediaByFileLink($assetPath, -3);
        $this->mediaResource->getManager()->flush($media);

        return $media;
    }

    /**
     * @param int $bannerId
     *
     * @return array
     */
    protected function getBannerData($bannerId)
    {
        $banner = $this->modelManager
            ->createQueryBuilder()
            ->select('banner', 'layers', 'elements')
            ->from(ContentBannerModel::class, 'banner')
            ->leftJoin('banner.layers', 'layers')
            ->leftJoin('layers.elements', 'elements')
            ->where('banner.id = :id')
            ->setParameter('id', $bannerId)
            ->orderBy('banner.id')
            ->addOrderBy('layers.position')
            ->addOrderBy('elements.position')
            ->getQuery()
            ->getSingleResult(2);

        if ($banner['mediaId']) {
            $media = $this->storeFrontMediaService->get($banner['mediaId'], $this->context);
            $banner['media'] = $this->legacyStructConverter->convertMediaStruct($media);
        }

        return $banner;
    }

    /**
     * @param array $layers
     * @param array $assets
     * @param array $importedAssets
     *
     * @return array
     */
    protected function processLayerImport(array $layers, array $assets, array &$importedAssets)
    {
        foreach ($layers as &$layer) {
            $elements = $layer['elements'];

            foreach ($elements as $layerKey => $layerElement) {
                if ($layerElement['name'] === 'image' && $layerElement['payload']) {
                    $payload = json_decode($layerElement['payload'], true);

                    if ($payload['mediaId']) {
                        if (!array_key_exists($payload['mediaId'], $importedAssets)) {
                            $assetPath = $assets[$payload['mediaId']];

                            $media = $this->doAssetImport($assetPath);
                            $importedAssets[$payload['mediaId']] = $media->getId();
                        } else {
                            $media = $this->getMediaById($importedAssets[$payload['mediaId']]);
                        }

                        $payload['mediaId'] = $media->getId();
                    }

                    $layerElement['payload'] = json_encode($payload);
                }
                $layer['elements'][$layerKey] = $layerElement;
            }
        }
        unset($layer);

        return $layers;
    }

    /**
     * @param array $layers
     * @param array $assets
     *
     * @return array
     */
    protected function processLayerExport(array $layers, array &$assets)
    {
        foreach ($layers as &$layer) {
            unset($layer['id'], $layer['contentBannerID']);
            $elements = $layer['elements'];

            foreach ($elements as $key => $layerElement) {
                unset($layerElement['id'], $layerElement['layerID']);

                if ($layerElement['name'] === 'image' && $layerElement['payload']) {
                    $payload = json_decode($layerElement['payload'], true);
                    $media = $this->getMediaById($payload['mediaId']);

                    if (!$media) {
                        continue;
                    }

                    $assetHash = md5($media->getId());
                    $assets[$assetHash] = $this->mediaService->getUrl($media->getPath());

                    $payload['mediaId'] = $assetHash;

                    $layerElement['payload'] = json_encode($payload);
                }
                $layer['elements'][$key] = $layerElement;
            }
        }
        unset($layer);

        return $layers;
    }

    /**
     * @param array $translations
     * @param array $bannerData
     */
    protected function importBannerTranslations(array $translations, array $bannerData)
    {
        $translator = new \Shopware_Components_Translation();
        $localeMapping = $this->getLocaleMapping();

        foreach ($bannerData['layers'] as $layer) {
            // map new layer ids to the translations
            if (isset($translations[$layer['position']])) {
                $translations[$layer['position']]['objectkey'] = $layer['id'];
            }

            foreach ($layer['elements'] as $key => $element) {
                // map new element ids to the translations
                $mapping = $layer['position'] . '_' . $key;
                if (!isset($translations[$mapping])) {
                    continue;
                }
                $translations[$mapping]['objectkey'] = $element['id'];
            }
        }

        foreach ($translations as $elem_translations) {
            // skip translation of objectkey (mapping) is missing
            if (!isset($elem_translations['objectkey'])) {
                continue;
            }

            // check for exact matches to prevent overrides
            $available_translations = [];
            foreach ($elem_translations as $translation) {
                if (!$this->isValidTranslation($translation)) {
                    continue;
                }

                if ($shopId = $this->getShopId($localeMapping, $translation)) {
                    $available_translations[] = $shopId;
                }
            }

            $imported = [];
            foreach ($elem_translations as $translation) {
                if (!$this->isValidTranslation($translation)) {
                    continue;
                }

                if (!$this->localeExists($localeMapping, $translation)) {
                    continue;
                }

                $exactMatch = false;
                $shopIds = [];

                if ($shopId = $this->getShopId($localeMapping, $translation)) {
                    $shopIds[] = $shopId;
                    $exactMatch = true;
                } else {
                    $shopIds = array_values($localeMapping[$translation['locale']]);
                }

                $objectdata = unserialize($translation['objectdata']);

                foreach ($shopIds as $shopId) {
                    // skip import if an exact match exists
                    if (in_array($shopId, $available_translations) && !$exactMatch) {
                        continue;
                    }

                    // skip import if a translation already exists
                    if (in_array($shopId, $imported)) {
                        continue;
                    }

                    $translator->write(
                        $shopId,
                        $translation['objecttype'],
                        $elem_translations['objectkey'],
                        $objectdata
                    );
                    $imported[] = $shopId;
                }
            }
        }
    }

    /**
     * @param array $layers
     *
     * @return array
     */
    protected function getBannerTranslations(array $layers)
    {
        $elementIdMapping = [];
        $layerIdMapping = [];

        // create mapping based on the layer and element position
        foreach ($layers as $layer) {
            // layers are only mapped by layer position
            $layerIdMapping[$layer['position']] = $layer['id'];

            foreach ($layer['elements'] as $key => $element) {
                $elementIdMapping[$layer['position'] . '_' . $key] = $element['id'];
            }
        }

        $elementIds = array_values($elementIdMapping);
        $elementIdMapping = array_flip($elementIdMapping);

        $layerIds = array_values($layerIdMapping);
        $layerIdMapping = array_flip($layerIdMapping);

        $translations = $this->modelManager->getConnection()->createQueryBuilder()
            ->select('translation.objectkey, translation.objecttype, translation.objectdata, locale.locale, shop.name as shop')
            ->from('s_core_translations', 'translation')
            ->leftJoin('translation', 's_core_shops', 'shop', 'translation.objectlanguage = shop.id')
            ->leftJoin('shop', 's_core_locales', 'locale', 'shop.locale_id = locale.id')
            ->where('translation.objecttype = "contentBannerElement" AND translation.objectkey IN (:ids)')
            ->orWhere('translation.objecttype = "digipubLink" AND translation.objectkey IN (:layer_ids) ')
            ->setParameter('ids', $elementIds, Connection::PARAM_INT_ARRAY)
            ->setParameter('layer_ids', $layerIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP);

        $mappedTranslations = [];

        foreach ($translations as $key => $translation) {
            $objecttype = $translation[0]['objecttype'];

            if ($objecttype === 'contentBannerElement') {
                $mappedTranslations[$elementIdMapping[$key]] = $translation;
            } elseif ($objecttype === 'digipubLink') {
                $mappedTranslations[$layerIdMapping[$key]] = $translation;
            }
        }

        return $mappedTranslations;
    }

    /**
     * @param $translation array
     *
     * @return bool
     */
    private function isValidTranslation($translation)
    {
        return isset($translation['objecttype'], $translation['objectdata'],
            $translation['locale'], $translation['shop']);
    }

    /**
     * @param $localeMapping array
     * @param $translation array
     *
     * @return bool
     */
    private function localeExists($localeMapping, $translation)
    {
        return isset($localeMapping[$translation['locale']]);
    }

    /**
     * @param $localeMapping array
     * @param $translation array
     *
     * @return int|null
     */
    private function getShopId($localeMapping, $translation)
    {
        if (!isset($localeMapping[$translation['locale']][$translation['shop']])) {
            return null;
        }

        return $localeMapping[$translation['locale']][$translation['shop']];
    }
}
