//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/ui/product-box-layout-selection"}
Ext.define('Shopware.apps.Advisor.view.details.ui.ProductBoxLayoutSelect', {
    extend: 'Shopware.apps.Base.view.element.ProductBoxLayoutSelect',
    helpText: '{s name="advisor_layout_help"}{/s}',

    alias: 'widget.advisor-details-ui.ProductBoxLayoutSelect',

    snippets: {
        showMatches: {
            label: '{s name="ui_productbox_layout_select_matches_title"}Show matches{/s}',
            description: '{s name="ui_productbox_layout_select_matches_description"}Displays in listing the matching characteristics.{/s}'
        },
        showMatchesAndMisses: {
            label: '{s name="ui_productbox_layout_select_show_all_title"}Show all{/s}',
            description: '{s name="ui_productbox_layout_select_show_all_description"}Displays in listing the matching and non-matching properties.{/s}'
        }
    },

    images: {
        showMatches: '{link file="backend/_resources/images/matches.jpg"}',
        showMatchesAndMisses: '{link file="backend/_resources/images/matches-misses.jpg"}'
    },

    forceSelection: true,

    /**
     * @overwrite
     */
    initComponent: function () {
        var me = this;

        me.callParent(arguments);

        me.getStore().insert(0, (me.createShowMatches()));
        me.getStore().insert(1, (me.createShowMatchesAndMisses()));
    },

    /**
     * @returns { Shopware.apps.Base.model.ProductBoxLayout }
     */
    createShowMatches: function () {
        return Ext.create('Shopware.apps.Base.model.ProductBoxLayout', {
            key: 'show_matches',
            label: this.snippets.showMatches.label,
            description: this.snippets.showMatches.description,
            image: this.images.showMatches
        });
    },

    /**
     * @returns { Shopware.apps.Base.model.ProductBoxLayout }
     */
    createShowMatchesAndMisses: function () {
        return Ext.create('Shopware.apps.Base.model.ProductBoxLayout', {
            key: 'show_matches_and_misses',
            label: this.snippets.showMatchesAndMisses.label,
            description: this.snippets.showMatchesAndMisses.description,
            image: this.images.showMatchesAndMisses
        });
    }
});
//{/block}