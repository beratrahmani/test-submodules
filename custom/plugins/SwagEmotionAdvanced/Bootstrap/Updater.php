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

namespace SwagEmotionAdvanced\Bootstrap;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;

class Updater
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @param Connection            $connection
     * @param MediaServiceInterface $mediaService
     */
    public function __construct(Connection $connection, MediaServiceInterface $mediaService)
    {
        $this->connection = $connection;
        $this->mediaService = $mediaService;
    }

    /**
     * @param string $oldVersion
     */
    public function update($oldVersion)
    {
        if (version_compare($oldVersion, '1.0.4', '<=')) {
            $this->addProductStreamField();
        }

        if (version_compare($oldVersion, '2.0.0', '<=')) {
            $this->changeValueTypSelectedProducts();
            $this->addVariantSelection();
        }

        if (version_compare($oldVersion, '3.1.0', '<=')) {
            $mediaMigration = new MediaPathMigration(
                $this->connection,
                $this->mediaService
            );

            $mediaMigration->migrate();
        }

        if (version_compare($oldVersion, '3.1.3', '<=')) {
            $this->fixShowListing();
        }
    }

    /**
     * Helper method to add the product-stream-field into the emotion component when updating the plugin.
     */
    private function addProductStreamField()
    {
        $sql = "SELECT id FROM `s_library_component` WHERE `x_type` = 'emotion-sideview-widget'";
        $componentId = $this->connection->executeQuery($sql)->fetchColumn();

        $sql = "INSERT INTO `s_library_component_field` (`componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`, `position`)
                VALUES (?, 'sideview_stream_selection', 'hiddenfield', '', '', '', '', '', '', '', '', '', 1, 10);";
        $this->connection->executeUpdate($sql, [$componentId]);
    }

    /**
     * Helper method to change the value type of the product selection
     */
    private function changeValueTypSelectedProducts()
    {
        $sql = "UPDATE `s_library_component_field` AS comp_field
                SET `comp_field`.`value_type` = '' 
                WHERE `comp_field`.`name` = 'sideview_selectedproducts'";

        $this->connection->executeUpdate($sql);

        $sql = "SELECT emoEle.`id`, `value`
                FROM s_emotion_element_value AS emoEle
                INNER JOIN s_library_component_field AS libComp
                  ON emoEle.fieldID = libComp.`id`
                  AND libComp.`name` = 'sideview_selectedproducts';";

        $emotionElements = $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_KEY_PAIR);

        $sql = 'UPDATE s_emotion_element_value
                SET `value` = :newValue
                WHERE `id` = :id;';

        foreach ($emotionElements as $id => $emotionElement) {
            $newValue = '|';
            /** @var array $products */
            $products = json_decode(json_decode($emotionElement));
            foreach ($products as $product) {
                $newValue .= $product->ordernumber . '|';
            }

            $this->connection->executeUpdate($sql, [
                'id' => $id,
                'newValue' => $newValue,
            ]);
        }
    }

    /**
     * Helper method to add the variant selection into the emotion component when updating the plugin
     */
    private function addVariantSelection()
    {
        $sql = "SELECT id FROM `s_library_component` WHERE `x_type` = 'emotion-sideview-widget'";
        $componentId = $this->connection->executeQuery($sql)->fetchColumn();

        $sql = "INSERT INTO `s_library_component_field` (`componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`, `translatable`, `position`)
                VALUES (?, 'sideview_selectedvariants', 'hiddenfield', '', '', '', '', '', '', '', '', '', 0, 0, 11);";
        $this->connection->executeUpdate($sql, [$componentId]);
    }

    /**
     * The product listing does not work with the storytelling mode. With this update the checkbox is hidden in the
     * backend module, but old settings might be invalid
     */
    private function fixShowListing()
    {
        $this->connection->update('s_emotion', ['show_listing' => 0], ['mode' => 'storytelling']);
    }
}
