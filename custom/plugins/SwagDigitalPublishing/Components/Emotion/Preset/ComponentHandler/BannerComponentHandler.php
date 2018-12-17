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

use Symfony\Component\HttpFoundation\ParameterBag;

class BannerComponentHandler extends AbstractComponentHandler
{
    const COMPONENT_TYPE = 'emotion-digital-publishing';

    const ELEMENT_BANNER_ID_KEY = 'digital_publishing_banner_id';
    const ELEMENT_DATA_KEY = 'digital_publishing_banner_data';

    /**
     * {@inheritdoc}
     */
    public function supports($componentType)
    {
        return $componentType === self::COMPONENT_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function import(array $element, ParameterBag $syncData)
    {
        if (!isset($element['data'])) {
            return $element;
        }

        return $this->processElementData($element, $syncData);
    }

    /**
     * {@inheritdoc}
     */
    public function export(array $element, ParameterBag $syncData)
    {
        if (!isset($element['data'])) {
            return $element;
        }

        return $this->prepareElementExport($element, $syncData);
    }

    /**
     * @param array $layers
     *
     * @return array
     */
    protected function getLayerElementsIds(array $layers)
    {
        $elementIds = [];

        foreach ($layers as $layer) {
            foreach ($layer['elements'] as $key => $element) {
                $elementIds[$layer['position'] . '_' . $key] = $element['id'];
            }
        }

        return $elementIds;
    }

    /**
     * @param array        $element
     * @param ParameterBag $syncData
     *
     * @return array
     */
    private function processElementData(array $element, ParameterBag $syncData)
    {
        $data = $element['data'];
        $assets = $syncData->get('assets', []);
        $banners = $syncData->get('banners', []);
        $importedAssets = $syncData->get('importedAssets', []);
        $importedBanners = $syncData->get('importedBanners', []);
        $bannerTranslations = $syncData->get('bannerTranslations', []);

        foreach ($data as $key => &$elementData) {
            if ($elementData['key'] === self::ELEMENT_BANNER_ID_KEY) {
                $bannerIdKey = $key;
            }
            if ($elementData['key'] === self::ELEMENT_DATA_KEY) {
                if (!array_key_exists($elementData['value'], $banners)) {
                    break;
                }
                if (!array_key_exists($elementData['value'], $importedBanners)) {
                    $bannerData = $banners[$elementData['value']];
                    $media = null;

                    if ($bannerData['mediaId'] && array_key_exists($bannerData['mediaId'], $assets)) {
                        if (!array_key_exists($bannerData['mediaId'], $importedAssets)) {
                            $assetPath = $assets[$bannerData['mediaId']];

                            $media = $this->doAssetImport($assetPath);
                            $importedAssets[$elementData['value']] = $media->getId();
                        } else {
                            $media = $this->getMediaById($importedAssets[$bannerData['mediaId']]);
                        }
                    }

                    if ($bannerData['layers']) {
                        $bannerData['layers'] = $this->processLayerImport($bannerData['layers'], $assets, $importedAssets);
                    }

                    $contentBanner = $this->createContentBanner($bannerData, $media);
                    $contentBanner = $this->getBannerData($contentBanner->getId());
                    $importedBanners[$elementData['value']] = $contentBanner['id'];

                    if (array_key_exists($elementData['value'], $bannerTranslations)) {
                        $translations = $bannerTranslations[$elementData['value']];

                        $this->importBannerTranslations($translations, $contentBanner);
                    }
                } else {
                    $contentBanner = $this->getBannerData($importedBanners[$elementData['value']]);
                }
                $elementData['value'] = json_encode(json_encode($contentBanner));
            }
        }
        unset($elementData);

        if (isset($contentBanner, $bannerIdKey)) {
            $data[$bannerIdKey]['value'] = $contentBanner['id'];
        }

        $syncData->set('importedAssets', $importedAssets);
        $syncData->set('importedBanners', $importedBanners);

        $element['data'] = $data;

        return $element;
    }

    /**
     * @param array        $element
     * @param ParameterBag $syncData
     *
     * @return array
     */
    private function prepareElementExport(array $element, ParameterBag $syncData)
    {
        $assets = $syncData->get('assets', []);
        $banners = $syncData->get('banners', []);
        $data = $element['data'];
        $bannerTranslations = $syncData->get('bannerTranslations', []);

        foreach ($data as &$elementData) {
            if ($elementData['key'] === self::ELEMENT_BANNER_ID_KEY) {
                unset($elementData['value']);
            }
            if ($elementData['key'] === self::ELEMENT_DATA_KEY) {
                // digipub values are saved double encoded!
                $banner = json_decode(json_decode($elementData['value'], true), true);
                $bannerId = $banner['id'];
                $bannerHash = md5($bannerId);
                $elementData['value'] = $bannerHash;

                $bannerData = $this->getBannerData($bannerId);
                unset($bannerData['id']);

                if ($bannerData['mediaId']) {
                    $assetHash = md5($bannerData['mediaId']);
                    $media = $this->getMediaById($bannerData['mediaId']);

                    if (!$media) {
                        break;
                    }
                    $assets[$assetHash] = $this->mediaService->getUrl($media->getPath());
                    $bannerData['mediaId'] = $assetHash;
                    unset($bannerData['media']);
                }

                if ($bannerData['layers']) {
                    $bannerTranslations[$bannerHash] = $this->getBannerTranslations($bannerData['layers']);
                    $bannerData['layers'] = $this->processLayerExport($bannerData['layers'], $assets);
                }

                $banners[$bannerHash] = $bannerData;
            }
        }
        unset($elementData);

        $syncData->set('assets', $assets);
        $syncData->set('banners', $banners);
        $syncData->set('bannerTranslations', $bannerTranslations);

        $element['data'] = $data;

        return $element;
    }
}
