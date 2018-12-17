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

use Shopware\Models\Emotion\Library\Component;

class EmotionComponentCreator
{
    /**
     * @param Component $component
     */
    public function addComponentFields(Component $component)
    {
        // Proxy field for the banner media selection
        $component->createHiddenField(
            [
                'name' => 'sideview_banner',
                'defaultValue' => '',
                'allowBlank' => false,
            ]
        );

        // Proxy field for the banner position field
        $component->createHiddenField(
            [
                'name' => 'sideview_bannerposition',
                'defaultValue' => 'center',
                'allowBlank' => false,
            ]
        );

        // Combobox for the sideview position
        $component->createComboBoxField(
            [
                'name' => 'sideview_position',
                'fieldLabel' => 'Sideview-Position',
                'displayField' => 'display',
                'valueField' => 'value',
                'allowBlank' => false,
            ]
        );

        // Combobox for the sideview size
        $component->createComboBoxField(
            [
                'name' => 'sideview_size',
                'fieldLabel' => 'Sideview-Größe',
                'supportText' => 'Diese Option gibt die Größe der Sideview auf Basis des Elementes an.',
                'displayField' => 'display',
                'valueField' => 'value',
                'allowBlank' => false,
            ]
        );

        // Combobox for the product accumulation
        $component->createComboBoxField(
            [
                'name' => 'sideview_product_type',
                'fieldLabel' => 'Listentyp',
                'supportText' => 'Definiert wie Produkte in der Sideview kumuliert werden.',
                'displayField' => 'display',
                'valueField' => 'value',
                'allowBlank' => false,
            ]
        );

        // Proxy field for the category selection
        $component->createHiddenField(
            [
                'name' => 'sideview_category_id',
                'defaultValue' => '3',
                'allowBlank' => false,
            ]
        );

        // Number field for the maximal product count
        $component->createNumberField(
            [
                'name' => 'sideview_max_products',
                'defaultValue' => 20,
                'allowBlank' => false,
                'fieldLabel' => 'Maximale Produktanzahl',
                'supportText' => 'Definiert wieviele Produkte ausgelesen werden, wenn die Produkte automatisch zusammen gestellt werden.',
            ]
        );

        // Checkbox which defines if arrows will be shown at the sideview slider
        $component->createCheckboxField(
            [
                'name' => 'sideview_show_arrows',
                'defaultValue' => true,
                'allowBlank' => true,
                'fieldLabel' => 'Pfeile anzeigen',
                'supportText' => 'Es werden Navigationspfeile für den Sideview-Slider angezeigt.',
            ]
        );

        // Checkbox which defines if the slider will automatically start scrolling
        $component->createCheckboxField(
            [
                'name' => 'sideview_auto_start',
                'defaultValue' => true,
                'allowBlank' => true,
                'fieldLabel' => 'Automatisch Starten',
                'supportText' => 'Der Slider innerhalb der Sideview startet automatisch.',
            ]
        );

        // Hidden field for saving the selected product stream id.
        $component->createHiddenField(
            [
                'name' => 'sideview_stream_selection',
                'allowBlank' => true,
            ]
        );

        // Proxy field for the selected products
        $component->createHiddenField(
            [
                'name' => 'sideview_selectedproducts',
                'defaultValue' => '',
                'allowBlank' => false,
            ]
        );

        // Proxy field for the selected variants
        $component->createHiddenField(
            [
                'name' => 'sideview_selectedvariants',
                'defaultValue' => '',
                'allowBlank' => false,
            ]
        );
    }
}
