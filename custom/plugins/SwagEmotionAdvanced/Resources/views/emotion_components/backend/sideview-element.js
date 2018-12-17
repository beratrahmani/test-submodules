// {namespace name="backend/swag_emotion_advanced/view/main"}
// {block name=backend/swag_emotion_advanced/view/components/side_view}
Ext.define('Shopware.apps.Emotion.view.components.SideView', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-sideview-widget',

    snippets: {
        'article_administration': '{s name=article_administration namespace=backend/emotion/view/components/article_slider}{/s}',

        'sideview_position': {
            'fieldLabel': '{s name="component/sideview/position/label"}{/s}'
        },

        'sideview_size': {
            'fieldLabel': '{s name="component/sideview/size/label"}{/s}',
            'supportText': '{s name="component/sideview/size/support"}{/s}'
        },

        'sideview_product_type': {
            'fieldLabel': '{s name="component/sideview/product_type/label"}{/s}',
            'supportText': '{s name="component/sideview/product_type/support"}{/s}'
        },

        'sideview_max_products': {
            'fieldLabel': '{s name="component/sideview/max_products/label"}{/s}',
            'supportText': '{s name="component/sideview/max_products/support"}{/s}'
        },

        'sideview_show_arrows': {
            'fieldLabel': '{s name="component/sideview/show_arrows/label"}{/s}'
        },

        'sideview_auto_start': {
            'fieldLabel': '{s name="component/sideview/auto_start/label"}{/s}'
        },

        'element_description': '{s name="components/sideview/element/description"}{/s}'
    },

    basePath: '{link file=""}',

    initComponent: function() {
        var me = this;
        me.callParent(arguments);

        me.customFields = me.getElements();

        me.elementFieldset.insert(7, me.createProductStreamField());

        me.bindStores();

        me.remapSupportTextToBoxLabel(me.customFields.showarrows);
        me.remapSupportTextToBoxLabel(me.customFields.autostart);

        me.categoryPathField = me.createCategoryPathSelection(me.elementFieldset);

        me.registerEvents();

        me.elementFieldset.add(me.addBannerMediaField());

        me.showHideComponentFields(null, me.customFields.producttype.getValue());

        me.bannerFile = me.getFieldByName('sideview_banner');
        if (me.bannerFile && me.bannerFile.value && me.bannerFile.value.length) {
            me.onSelectMedia('', me.bannerFile.value);
        }

        me.items.get(0).items.get(0).update(me.snippets.element_description);

        me.bannerPositionField = me.getFieldByName('sideview_bannerposition');
    },

    /**
     * overrides the default behaviour for the product selection to use the Shopware component
     *
     * @override
     */
    pushItemToElements: function(item, items) {
        var me = this,
            factory = Ext.create('Shopware.attribute.SelectionFactory');

        if (item.name === 'sideview_selectedproducts') {
            me.articleSearch = Ext.create('Shopware.form.field.ProductGrid', {
                name: item.name,
                fieldId: item.fieldId,
                store: factory.createEntitySearchStore('Shopware\\Models\\Article\\Article'),
                searchStore: factory.createEntitySearchStore('Shopware\\Models\\Article\\Article'),
                fieldLabel: me.snippets.article_administration,
                labelWidth: 170
            });

            items.push(me.articleSearch);

            return items;
        } else if (item.name === 'sideview_selectedvariants') {
            me.variantSearch = Ext.create('Shopware.form.field.ProductGrid', {
                name: item.name,
                fieldId: item.fieldId,
                store: factory.createEntitySearchStore('Shopware\\Models\\Article\\Detail'),
                searchStore: factory.createEntitySearchStore('Shopware\\Models\\Article\\Detail'),
                fieldLabel: me.snippets.article_administration,
                labelWidth: 170
            });

            items.push(me.variantSearch);

            return items;
        }

        return me.callParent(arguments);
    },

    addBannerMediaField: function() {
        var me = this,
            value = me.customFields.banner.getValue();

        me.mediaSelection = Ext.create('Shopware.form.field.MediaSelection', {
            fieldLabel: '{s name="component/sideview/banner/label"}{/s}',
            value: value,
            albumId: -3,
            listeners: {
                scope: me,
                selectMedia: me.onSelectMedia
            },
            labelWidth: 170
        });

        return me.mediaSelection;
    },

    getFieldByName: function(name) {
        var me = this,
            items = me.elementFieldset.items.items,
            storeField;

        Ext.each(items, function(item) {
            if (item.name === name) {
                storeField = item;
                return false;
            }
        });

        return storeField;
    },

    onSelectMedia: function(comp, selection) {
        var me = this;

        selection = Ext.isArray(selection) ? selection[0].get('path') : selection;

        me.customFields.banner.setValue(selection);

        if (!me.previewFieldset) {
            me.previewFieldset = me.createPreviewImage(selection);
            me.add(me.previewFieldset);
        } else {
            me.previewImage.update({ src: selection });
        }
    },

    getElements: function() {
        var me = this,
            customFields = {},
            name;

        me.elementFieldset.items.each(function(item) {
            if (!item.hasOwnProperty('name')) {
                return;
            }

            name = item.name;
            name = name.replace('sideview_', '');
            name = name.replace('_', '');

            customFields[name] = item;
        });

        return customFields;
    },

    bindStores: function() {
        var me = this,
            fields = me.customFields;

        fields.position.bindStore(me.getPositionStore());
        fields.size.bindStore(me.getSizeStore());
        fields.producttype.bindStore(me.getProductTypeStore());

        // We have to trigger the `select` method to display the correct value
        fields.position.select(fields.position.getValue());
        fields.size.select(fields.size.getValue());
        fields.producttype.select(fields.producttype.getValue());

        return true;
    },

    getPositionStore: function() {
        return Ext.create('Ext.data.Store', {
            fields: ['value', 'display'],
            data: [
                { value: 'right', display: '{s name="store/display/position/right"}{/s}' },
                { value: 'bottom', display: '{s name="store/display/position/bottom"}{/s}' }
            ]
        });
    },

    getSizeStore: function() {
        return Ext.create('Ext.data.Store', {
            fields: ['value', 'display'],
            data: [
                { value: 'fullsize', display: '{s name="store/display/size/fullsize"}{/s}' },
                { value: 'halfsize', display: '{s name="store/display/size/halfsize"}{/s}' }
            ]
        });
    },

    getProductTypeStore: function() {
        return Ext.create('Ext.data.Store', {
            fields: ['value', 'display'],
            data: [
                { value: 'selected_products', display: '{s name="article_slider_type/store/selected_article" namespace="backend/emotion/view/components/article_slider_type"}{/s}' },
                { value: 'selected_variants', display: '{s name="article_slider_type/store/selected_variant" namespace="backend/emotion/view/components/article_slider_type"}{/s}' },
                { value: 'newcomer', display: '{s name="article_slider_type/store/newcomer" namespace="backend/emotion/view/components/article_slider_type"}{/s}' },
                { value: 'topseller', display: '{s name="article_slider_type/store/topseller" namespace="backend/emotion/view/components/article_slider_type"}{/s}' },
                { value: 'price_asc', display: '{s name="article_slider_type/store/price_asc" namespace="backend/emotion/view/components/article_slider_type"}{/s}' },
                { value: 'price_desc', display: '{s name="article_slider_type/store/price_desc" namespace="backend/emotion/view/components/article_slider_type"}{/s}' },
                { value: 'product_stream', display: '{s name="article_slider_type/store/product_stream" namespace="backend/emotion/view/components/article_slider_type"}{/s}' }
            ]
        });
    },

    createCategoryPathSelection: function(fieldset) {
        var me = this,
            categorySelection = Ext.create('Shopware.apps.Emotion.view.components.fields.CategorySelection', {
                fieldLabel: '{s name="settings/select_category_field" namespace="backend/emotion/view/detail"}{/s}',
                scope: me,
                hidden: true,
                labelWidth: 170
            });

        categorySelection.setValue(~~(1 * me.customFields.categoryid.getValue()));
        fieldset.insert(5, categorySelection);

        return categorySelection;
    },

    remapSupportTextToBoxLabel: function(el) {
        el.boxLabel = el.supportText;
        el.addListener('afterrender', function() {
            if (!el.hasOwnProperty('supportTextEl')) {
                return false;
            }
            el.supportTextEl.dom.innerHTML = '';
        });
    },

    registerEvents: function() {
        var me = this;

        me.categoryPathField.on('change', function(field, value) {
            // Write back the selected value to the hidden field as a string
            me.customFields.categoryid.setValue(value + '');
        });

        me.customFields.producttype.on('change', Ext.bind(me.showHideComponentFields, me));
    },

    showHideComponentFields: function(field, value) {
        var me = this,
            productSelection = (value === 'selected_products'),
            variantSelection = (value === 'selected_variants'),
            streamSelection = (value === 'product_stream');

        me.customFields.maxproducts.setVisible(!productSelection && !variantSelection && !streamSelection);
        me.categoryPathField.setVisible(!productSelection && !variantSelection && !streamSelection);

        me.articleSearch.setVisible(productSelection);
        me.variantSearch.setVisible(variantSelection);

        me.productStreamField.setVisible(streamSelection);
        me.productStreamField.allowBlank = !streamSelection;
    },

    /**
     * Creates the product stream selection field.
     *
     * @returns { Shopware.apps.Emotion.view.components.fields.ProductStreamSelection|* }
     */
    createProductStreamField: function() {
        var me = this;

        me.productStreamField = Ext.create('Shopware.form.field.ProductStreamSelection', {
            value: me.customFields['streamselection'].getValue(),
            labelWidth: 170
        });

        me.productStreamField.hide();
        me.productStreamField.on('change', Ext.bind(me.onStreamSelect, me));

        return me.productStreamField;
    },

    /**
     * Updates the hidden input with the stream id when a stream gets selected.
     *
     * @param field
     * @param value
     */
    onStreamSelect: function(field, value) {
        var me = this;

        me.customFields['streamselection'].setValue(value);
    },

    createPreviewImage: function(media) {
        var me = this;

        me.previewImage = Ext.create('Ext.container.Container', {
            tpl: me.getPreviewImageTemplate(),
            data: {
                src: media
            },
            listeners: {
                'afterrender': me.registerPreviewPositionEvents.bind(me)
            }
        });

        return Ext.create('Ext.form.FieldSet', {
            title: '{s name=component/sideview/banner/preview}Preview image{/s}',
            items: [me.previewImage]
        });
    },

    registerPreviewPositionEvents: function() {
        var me = this,
            el = me.previewImage.getEl();

        el.on('click', function(event, target) {
            var $target = Ext.get(target),
                position = $target.getAttribute('data-position');

            Ext.each(el.dom.querySelectorAll('.preview-image--col'), function() {
                this.classList.remove('is--active');
            });
            $target.addCls('is--active');

            me.bannerPositionField.setValue(position);
        }, me, { delegate: '.preview-image--col' });

        if (me.bannerPositionField) {
            var val = me.bannerPositionField.getValue();

            Ext.each(el.dom.querySelectorAll('.preview-image--col'), function() {
                this.classList.remove('is--active');
            });

            el.dom.querySelector('.preview-image--col[data-position="' + val + '"]').classList.add('is--active');
        }
    },

    getPreviewImageTemplate: function() {
        return new Ext.Template(
            '<div class="preview-image--container">',
            '<img class="preview-image--media" src="[src]" alt="Preview Banner">',

            '<div class="preview-image--grid">',
            '<div class="preview-image--row">',
            '<div class="preview-image--col" data-position="top left">&nbsp;</div>',
            '<div class="preview-image--col" data-position="top center">&nbsp;</div>',
            '<div class="preview-image--col" data-position="top right">&nbsp;</div>',
            '</div>',

            '<div class="preview-image--row">',
            '<div class="preview-image--col" data-position="center left">&nbsp;</div>',
            '<div class="preview-image--col is--active" data-position="center">&nbsp;</div>',
            '<div class="preview-image--col" data-position="center right">&nbsp;</div>',
            '</div>',

            '<div class="preview-image--row">',
            '<div class="preview-image--col" data-position="bottom left">&nbsp;</div>',
            '<div class="preview-image--col" data-position="bottom center">&nbsp;</div>',
            '<div class="preview-image--col" data-position="bottom right">&nbsp;</div>',
            '</div>',
            '</div>',
            '</div>'
        );
    }
});
// {/block}
