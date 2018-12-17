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

class BannerSliderComponentHandler extends AbstractComponentHandler
{
    const COMPONENT_TYPE = 'emotion-digital-publishing-slider';

    const ELEMENT_PAYLOAD_KEY = 'digital_publishing_slider_payload';
    const ELEMENT_DATA_KEY = 'digital_publishing_slider_preview_data';

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
        $previewBanner = [];

        foreach ($data as $key => &$elementData) {
            if ($elementData['key'] === self::ELEMENT_PAYLOAD_KEY) {
                $payloadData = json_decode($elementData['value'], true);

                foreach ($payloadData as $bannerIndex => &$item) {
                    if (!array_key_exists($item, $banners)) {
                        continue;
                    }
                    if (!array_key_exists($item, $importedBanners)) {
                        $bannerData = $banners[$item];
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

                        if ($bannerIndex === 0) {
                            $previewBanner = $contentBanner;
                        }

                        $importedBanners[$item] = $contentBanner['id'];
                        $bannerPayload = [
                            'id' => $contentBanner['id'],
                            'name' => $contentBanner['name'],
                            'bgType' => $contentBanner['bgType'],
                            'bgOrientation' => $contentBanner['bgOrientation'],
                            'bgMode' => $contentBanner['bgMode'],
                            'bgColor' => $contentBanner['bgColor'],
                        ];

                        if ($media) {
                            $bannerPayload['mediaId'] = $media->getId();
                        }

                        if (array_key_exists($item, $bannerTranslations)) {
                            $translations = $bannerTranslations[$item];

                            $this->importBannerTranslations($translations, $contentBanner);
                        }
                    } else {
                        $contentBanner = $this->getBannerData($importedBanners[$item]);
                        $bannerPayload = [
                            'id' => $contentBanner['id'],
                            'name' => $contentBanner['name'],
                            'bgType' => $contentBanner['bgType'],
                            'bgOrientation' => $contentBanner['bgOrientation'],
                            'bgMode' => $contentBanner['bgMode'],
                            'bgColor' => $contentBanner['bgColor'],
                            'mediaId' => $contentBanner['mediaId'],
                        ];
                    }
                    $item = $bannerPayload;

                    if ($contentBanner && $bannerIndex === 0) {
                        $elementDataBannerId = $contentBanner['id'];
                    }
                }
                unset($item);

                $elementData['value'] = json_encode($payloadData);
            }
            if ($elementData['key'] === self::ELEMENT_DATA_KEY) {
                $elementDataIndex = $key;
            }
        }
        unset($elementData);

        if (isset($elementDataIndex, $elementDataBannerId)) {
            $data[$elementDataIndex]['value'] = json_encode(json_encode($previewBanner));
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
            if ($elementData['key'] === self::ELEMENT_PAYLOAD_KEY) {
                $payloadData = json_decode($elementData['value'], true);

                foreach ($payloadData as &$item) {
                    $bannerId = $item['id'];
                    $bannerHash = md5($bannerId);
                    $item = $bannerHash;

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
                unset($item);
                $elementData['value'] = json_encode($payloadData);
            }
            if ($elementData['key'] === self::ELEMENT_DATA_KEY) {
                unset($elementData['value']);
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
