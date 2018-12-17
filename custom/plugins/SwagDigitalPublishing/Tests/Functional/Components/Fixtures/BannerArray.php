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
    '_dc' => '1489150582424',
    'module' => 'backend',
    'controller' => 'SwagContentBanner',
    'action' => 'update',
    'id' => 44444,
    'name' => 'Hintergrund',
    'bgType' => 'image',
    'bgOrientation' => 'center center',
    'bgMode' => 'cover',
    'bgColor' => '#001DF7',
    'mediaId' => 781,
    'layers' => [
        [
            'id' => 44444,
            'contentBannerID' => 44444,
            'position' => 0,
            'label' => 'Ebene1',
            'width' => 'auto',
            'height' => 'auto',
            'marginTop' => 0,
            'marginRight' => 0,
            'marginBottom' => 0,
            'marginLeft' => 0,
            'borderRadius' => 0,
            'orientation' => 'center left',
            'bgColor' => '',
            'link' => '',
            'elements' => [
                [
                    'id' => 134444,
                    'layerID' => 44444,
                    'position' => 1,
                    'name' => 'image',
                    'label' => 'Bild',
                    'payload' => '{"mediaId":561,"alt":"","maxWidth":100,"maxHeight":100,"orientation":"left","paddingTop":0,"paddingLeft":0,"paddingChain":false,"paddingRight":0,"paddingBottom":0,"class":""}',
                ],
            ],
        ],
        [
            'id' => 944444,
            'contentBannerID' => 44444,
            'position' => 1,
            'label' => 'Ebene2',
            'width' => 'auto',
            'height' => 'auto',
            'marginTop' => 0,
            'marginRight' => 0,
            'marginBottom' => 0,
            'marginLeft' => 0,
            'borderRadius' => 0,
            'orientation' => 'center center',
            'bgColor' => '',
            'link' => '',
            'elements' => [
                [
                    'id' => 1444444,
                    'layerID' => 944444,
                    'position' => 0,
                    'name' => 'image',
                    'label' => 'Bild',
                    'payload' => '{"mediaId":553,"alt":"","maxWidth":100,"maxHeight":100,"orientation":"left","paddingTop":0,"paddingLeft":0,"paddingChain":false,"paddingRight":0,"paddingBottom":0,"class":""}',
                ],
            ],
        ],
    ],
];
