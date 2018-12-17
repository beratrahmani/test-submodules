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

namespace SwagProductAdvisor\Components\Helper;

/**
 * Class BackendStreamHelper
 */
interface BackendStreamHelperInterface
{
    /**
     * @param $streamId
     *
     * @return float|int
     */
    public function getMaxPriceByStreamIds($streamId);

    /**
     * @param int    $streamId
     * @param string $propertyId
     *
     * @return array
     */
    public function getPropertyValuesByStreamAndPropertyId($streamId, $propertyId);

    /**
     * @param int $streamId
     *
     * @return array
     */
    public function getPropertiesByStreamId($streamId);

    /**
     * @param int $streamId
     *
     * @return array
     */
    public function getManufacturerByStreamIds($streamId);

    /**
     * @param int    $streamId
     * @param string $columnName
     *
     * @return array
     */
    public function getAttributeValuesByStreamIdAndAttributeColumnName($streamId, $columnName);
}
