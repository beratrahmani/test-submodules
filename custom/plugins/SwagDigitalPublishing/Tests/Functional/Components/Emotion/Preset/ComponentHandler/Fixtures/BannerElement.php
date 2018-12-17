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

return [
    'componentId' => '14',
    'startRow' => 1,
    'startCol' => 1,
    'endRow' => 1,
    'endCol' => 1,
    'cssClass' => '',
    'viewports' => [
            [
                'alias' => 'xs',
                'startRow' => 1,
                'startCol' => 1,
                'endRow' => 1,
                'endCol' => 1,
                'visible' => true,
            ], [
                'alias' => 's',
                'startRow' => 1,
                'startCol' => 1,
                'endRow' => 1,
                'endCol' => 1,
                'visible' => true,
            ], [
                'alias' => 'm',
                'startRow' => 1,
                'startCol' => 1,
                'endRow' => 1,
                'endCol' => 1,
                'visible' => true,
            ], [
                'alias' => 'l',
                'startRow' => 1,
                'startCol' => 1,
                'endRow' => 1,
                'endCol' => 1,
                'visible' => true,
            ], [
                'alias' => 'xl',
                'startRow' => 1,
                'startCol' => 1,
                'endRow' => 1,
                'endCol' => 1,
                'visible' => true,
            ],
        ],
    'component' => [
            'id' => '14',
            'name' => 'Digital Publishing',
            'convertFunction' => null,
            'description' => '',
            'template' => 'component_digital_publishing',
            'cls' => 'emotion--digital-publishing',
            'xType' => 'emotion-digital-publishing',
            'fields' => [
                    [
                        'id' => '104',
                        'componentId' => '14',
                        'name' => 'digital_publishing_banner_id',
                        'fieldLabel' => '',
                        'xType' => 'hiddenfield',
                        'valueType' => '',
                        'supportText' => '',
                        'store' => '',
                        'displayField' => '',
                        'valueField' => '',
                        'defaultValue' => '',
                        'allowBlank' => 0,
                        'helpTitle' => '',
                        'helpText' => '',
                        'translatable' => 0,
                        'position' => 0,
                    ], [
                        'id' => '105',
                        'componentId' => '14',
                        'name' => 'digital_publishing_banner_data',
                        'fieldLabel' => '',
                        'xType' => 'hiddenfield',
                        'valueType' => 'json',
                        'supportText' => '',
                        'store' => '',
                        'displayField' => '',
                        'valueField' => '',
                        'defaultValue' => '',
                        'allowBlank' => 1,
                        'helpTitle' => '',
                        'helpText' => '',
                        'translatable' => 0,
                        'position' => 1,
                    ],
                ],
            'plugin' => 'SwagDigitalPublishing',
            'pluginId' => '63',
        ],
    'data' => [
            [
                'componentId' => '14',
                'fieldId' => '104',
                'value' => '3500993',
                'key' => 'digital_publishing_banner_id',
                'valueType' => '',
            ], [
                'componentId' => '14',
                'fieldId' => '105',
                'value' => '"{\"id\":3500993,\"name\":\"Banner\",\"bgType\":\"image\",\"bgOrientation\":\"center center\",\"bgMode\":\"cover\",\"bgColor\":\"\",\"mediaId\":439,\"layers\":[]}"',
                'key' => 'digital_publishing_banner_data',
                'valueType' => 'json',
            ],
        ],
    'syncKey' => 'preset-element-59019dba754f64.30391811',
];
