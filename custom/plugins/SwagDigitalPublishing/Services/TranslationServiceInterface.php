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

interface TranslationServiceInterface
{
    /**
     * @param array                $layer
     * @param ShopContextInterface $context
     *
     * @return array
     */
    public function translate(array $layer, ShopContextInterface $context);

    /**
     * @param array $layer
     * @param int   $languageId
     * @param int   $fallbackId
     *
     * @return array
     */
    public function translateElements(array $layer, $languageId, $fallbackId);

    /**
     * @param array $layer
     * @param int   $languageId
     *
     * @return array
     */
    public function translateLink(array $layer, $languageId);
}
