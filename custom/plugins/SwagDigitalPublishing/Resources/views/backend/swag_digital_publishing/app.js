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

// {namespace name=backend/plugins/swag_digital_publishing/main}
// {block name="backend/swag_digital_publishing/app"}
Ext.define('Shopware.apps.SwagDigitalPublishing', {

    extend: 'Enlight.app.SubApplication',

    name: 'Shopware.apps.SwagDigitalPublishing',

    loadPath: '{url action=load}',
    bulkLoad: true,

    /**
     * Open the module in standard or selection mode.
     *
     * @string
     * @default standard
     */
    mode: 'standard',

    controllers: [ 'Main' ],
    stores: [ 'ContentBanner' ],
    models: [ 'ContentBanner', 'Layer', 'Element' ],
    views: [
        'main.Window',
        'main.Container',
        'main.Listing',
        'selection.Window',
        'selection.Listing',
        'editor.Container',
        'editor.fields.MediaField',
        'editor.fields.PaddingField',
        'editor.fields.ShadowField',
        'editor.fields.TextFontSelectField',
        'editor.fields.TextOrientationField',
        'editor.fields.TextStyleField',
        'editor.fields.TextTypeField',
        'editor.settings.Banner',
        'editor.settings.Layer',
        'editor.elements.AbstractElementHandler',
        'editor.elements.TextElementHandler',
        'editor.elements.ButtonElementHandler',
        'editor.elements.ImageElementHandler'
    ],

    launch: function() {
        return this.getController('Main').mainWindow;
    }
});
// {/block}
