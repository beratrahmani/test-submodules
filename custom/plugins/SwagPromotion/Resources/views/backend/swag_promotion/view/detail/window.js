
//{namespace name="backend/swag_promotion/main"}
//{block name="backend/promotion/view/detail/window"}
Ext.define('Shopware.apps.SwagPromotion.view.detail.Window', {
    extend: 'Shopware.window.Detail',
    alias: 'widget.swag-promotion-detail-window',

    title: '{s namespace="backend/swag_promotion/snippets" name=promotionTitleDetails}Promotion details{/s}',
    height: 600,
    width: 1280,

    configure: function () {
        var me = this;
        Shopware.app.Application.on(
            'main-after-update-record-on-save',
            me.modifyRecordBeforeSave
        );

        return {
            translationKey: 'swag-promotion-settings',
            controller: 'SwagPromotion',
            associations: ['promotionRules']
        };
    },

    /**
     * @override
     */
    initComponent: function () {
        var me = this;
        me.callParent(arguments);

        me.title = me.title + ': ' + me.record.get('name');

        me.on('resize', function() {
            me.doAutoRender();
        });
    },

    /**
     * @param controller
     * @param window
     * @param record
     * @param form
     */
    modifyRecordBeforeSave: function (controller, window, record, form) {
        if (form.down('[name=voucherId]').getValue() == null) {
            record.set('voucherId', -1);
        }
    },

    /**
     * @override
     * @inheritDoc
     */
    createTabItem: function (association) {
        var me = this,
            item = me.callParent(arguments);

        if (association && association.isBaseRecord) {
            item.title = '{s namespace="backend/swag_promotion/snippets" name=promotionConfigurationTabTitle}Promotion configuration{/s}';
        }

        return item;
    },

    /**
     * @override
     * @inheritDoc
     */
    createTabItems: function () {
        var me = this,
            items = me.callParent(arguments);

        return [
            items[0],
            items[1]
        ];
    }
});
//{/block}
