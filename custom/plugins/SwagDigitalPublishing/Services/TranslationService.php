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

use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class TranslationService implements TranslationServiceInterface
{
    /**
     * @var \Shopware_Components_Translation
     */
    private $translator;

    /**
     * @param \Shopware_Components_Translation $translator
     */
    public function __construct(\Shopware_Components_Translation $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function translate(array $layer, ShopContextInterface $context)
    {
        $languageId = $context->getShop()->getId();
        $languageFallbackId = $context->getShop()->getFallbackId();

        $layer = $this->translateElements($layer, $languageId, $languageFallbackId);

        return $this->translateLink($layer, $languageId);
    }

    /**
     * {@inheritdoc}
     */
    public function translateElements(array $layer, $languageId, $fallbackId)
    {
        foreach ($layer['elements'] as &$element) {
            $translation = $this->translator->readWithFallback(
                $languageId,
                $fallbackId,
                'contentBannerElement',
                $element['id']
            );

            if (!empty($translation)) {
                $element = array_merge($element, $translation);
            }
        }

        return $layer;
    }

    /**
     * {@inheritdoc}
     */
    public function translateLink(array $layer, $languageId)
    {
        if (empty($layer['link'])) {
            return $layer;
        }

        $translation = $this->translator->read(
            $languageId,
            'digipubLink',
            $layer['id']
        );

        if ($translation) {
            $layer['link'] = $translation['link'];
        }

        return $layer;
    }
}
