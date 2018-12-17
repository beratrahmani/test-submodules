
//{namespace name="backend/swag_promotion/main"}
//{block name="backend/swag_rule_tree/view/form"}
Ext.define('Shopware.apps.SwagRuleTree.view.Form', {
    extend: 'Ext.form.Panel',
    region: 'center',
    flex: 1,
    title: '{s namespace="backend/swag_promotion/snippets" name="promotionRuleConfigurationTitle"}Rule configuration{/s}',
    bodyStyle: { "background-color": "white" },
    productsOnly: false,

    initComponent: function () {
        var me = this;

        me.defaultStore = Ext.create('Ext.data.Store', {
            data: [].concat(this.getDefaultTypes()).concat(this.productsOnly ? this.getProductType() : [], this.productsOnly ? [] : this.getAdditionalTypes()),
            fields: ['type', 'name']
        });

        me.rootStore = Ext.create('Ext.data.Store', {
            data: [].concat(this.getDefaultTypes()),
            fields: ['type', 'name']
        });

        me.items = [
            {
                xtype: 'label',
                html: '<center><b>{s name=noRuleSelected}No rule selected{/s}</b></center>'
            },
            {
                xtype: 'fieldset',
                title: '{s name=selectRuleType}Select rule type{/s}',
                name: 'ruleType',
                hidden: true,
                margin: 10,
                items: [
                    me.getCombo()
                ]
            },
            me.getRuleFieldSet(),
            me.getStreamFieldSet()
        ];

        me.callParent(arguments);
    },

    showElements: function () {
        var me = this;

        switch (me.typeCombo.getValue()) {
            case 'and':
            case 'or':
            case 'true':
                me.down('label').hide();
                me.down('fieldset').show();
                me.down('fieldset[name=ruleType]').show();
                me.down('fieldset[name=rule]').hide();
                me.down('fieldset[name=streamFieldSet]').hide();
                break;
            case 'basketCompareRule':
                me.down('label').hide();
                me.down('fieldset[name=ruleType]').show();
                me.down('fieldset[name=rule]').show();
                me.down('fieldset[name=rule] button').hide();
                me.down('fieldset[name=streamFieldSet]').hide();
                break;
            case 'stream':
                me.down('fieldset[name=ruleType]').show();
                me.down('fieldset[name=streamFieldSet]').show();
                me.down('label').hide();
                me.down('fieldset[name=rule]').hide();
                me.down('fieldset[name=rule] button').hide();
                break;
            case null:
                me.down('label').hide();
                me.down('fieldset[name=ruleType]').show();
                me.down('fieldset[name=rule]').hide();
                me.down('fieldset[name=streamFieldSet]').hide();
                break;
            default:
                me.down('label').hide();
                me.down('fieldset[name=ruleType]').show();
                me.down('fieldset[name=rule]').show();
                me.down('fieldset[name=streamFieldSet]').hide();
        }
    },

    getRuleFieldSet: function () {
        var me = this;

        return {
            xtype: 'fieldset',
            name: 'rule',
            margin: 10,
            hidden: true,
            layout: 'hbox',
            anchor: '100%',
            title: '{s name=defineRule}Define your rule{/s}',
            items: me.getComboItems()
        }
    },

    getStreamFieldSet: function () {
        var me = this;

        return {
            xtype: 'fieldset',
            name: 'streamFieldSet',
            margin: 10,
            hidden: true,
            anchor: '100%',
            title: '{s name=productStreams}Select the streams{/s}',
            items: me.getStreamCombo()
        }
    },

    getComboItems: function () {
        var me = this;
        return [
            me.getFieldCombo(),
            me.getOperatorCombo(),
            me.getValueEntry(),
            me.getValueSearchButton()
        ]
    },

    getFieldCombo: function () {
        var me = this;

        me.fieldCombo = Ext.create('Ext.form.field.ComboBox', {
            displayField: 'name',
            valueField: 'field',
            editable: false,
            flex: 1
        });

        return me.fieldCombo;
    },

    getOperatorCombo: function () {
        var me = this;

        me.operatorCombo = Ext.create('Ext.form.field.ComboBox', {
            displayField: 'name',
            valueField: 'operator',
            editable: false,
            anchor: '33%'
        });

        return me.operatorCombo;
    },

    getStreamCombo: function () {
        var me = this;

        return [
            me.streamCombo = Ext.create('Shopware.form.field.ProductStreamSelection', {
                name: 'streamId',
                labelWidth: 150,
                anchor: '100%'
            })
        ]
    },

    getValueEntry: function () {
        var me = this;

        me.value = Ext.create('Ext.form.field.Text', {
            anchor: '33%'
        });

        return me.value;
    },

    getValueSearchButton: function () {
        var me = this;

        me.searchButton = Ext.create('Ext.button.Button', {
            text: '{s namespace="backend/swag_promotion/snippets" name="promotionRulesearchButon"}Selection{/s}',
            cls: Ext.baseCSSPrefix + 'form-mediamanager-btn small secondary',
            iconCls: 'article--overview',
            padding: '5px',
            handler: function () {
                if (Shopware.app.SwagPromotionSearchWindow) {
                    try {
                        Shopware.app.SwagPromotionSearchWindow.destroy();
                    } catch (e) {
                    }
                }
                Shopware.app.SwagPromotionSearchWindow = Ext.create('Shopware.apps.SwagRuleTree.view.SearchWindow.Window', {
                    field: me.fieldCombo.getValue(),
                    preSelected: me.value.getValue(),
                    callback: function (value) {
                        me.value.setValue(value);
                    }
                }).show();
            }
        });

        return me.searchButton;
    },

    getCombo: function () {
        var me = this;

        me.typeCombo = Ext.create('Ext.form.field.ComboBox', {
            displayField: 'name',
            helpText: '{s name="selectRuleTypeHelpText"}Select the type of the rule you want to define.{/s}',
            editable: false,
            anchor: "100%",
            valueField: 'type',
            store: me.defaultStore
        });

        return me.typeCombo;
    },

    getDefaultTypes: function () {
        return [
            { "type": "and", "name": "{s name=andExplained}AND: If all subrules are true{/s}" },
            { "type": "or", "name": "{s name=orExplained}OR: If one or more subrules are true{/s}" },
            { "type": "true", "name": "{s name=ruleTrue}Always true{/s}" }
        ];
    },

    getProductType: function () {
        return [
            { "type": "stream", "name": "{s name=streamRule}Stream rule{/s}" },
            { "type": "productCompareRule", "name": "{s name=productRule}Product rule{/s}" }
        ];
    },

    getAdditionalTypes: function () {
        return [
            { "type": "basketCompareRule", "name": "{s name=basketRule}Basket rule{/s}" },
            { "type": "customerCompareRule", "name": "{s name=userRule}User rule{/s}" }
        ];
    }
});
//{/block}
