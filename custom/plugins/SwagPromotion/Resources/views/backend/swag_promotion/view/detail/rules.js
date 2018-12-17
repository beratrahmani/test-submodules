
//{namespace name="backend/swag_promotion/main"}
//{block name="backend/promotion/view/detail/rules"}
Ext.define('Shopware.apps.SwagPromotion.view.detail.Rules', {
    extend: 'Shopware.model.Container',
    padding: 20,

    layout: 'anchor',
    defaults: {
        anchor: '100%'
    },

    initComponent: function () {
        var me = this;
        me.on('rules-after-init-component', function (obj) {
            obj.title = '{s namespace="backend/swag_promotion/snippets" name="promotionRuleTabTitle"}Promotion rule{/s}';
        });
        me.callParent(arguments);
    },

    configure: function () {
        var me = this;

        me.associationItems = [];
        me.blacklistedPromotions = [];

        return {

            fieldAlias: 'promotionRules',
            associations: [
                'customerGroups',
                'shops',
                'doNotAllowLater',
                'doNotRunAfter'
            ],
            fieldSets: [
                {
                    isRuleFieldSet: true,
                    title: '{s namespace="backend/swag_promotion/snippets" name="promotionRuleTabTitle"}Promotion rule{/s}',
                    layout: 'fit',
                    fields: {
                        rules: {
                            fieldLabel: '',
                            width: '100%'
                        }
                    }
                },
                {
                    isRebateRuleFieldSet: true,
                    layout: 'fit',
                    identifier: 'productRuleFieldset',
                    title: '{s namespace="backend/swag_promotion/snippets" name="productValidForDiscount"}Products valid for discount{/s}',
                    fields: {
                        applyRules: {
                            fieldLabel: '',
                            productsOnly: true,
                            identifier: "applyRules",
                            width: '100%'
                        }
                    }
                }
            ]
        };
    },

    /**
     * @Override
     */
    createModelFieldSet: function (modelName, fields, customConfig) {
        var me = this,
            fieldSet = me.callParent(arguments);

        if (arguments[2].isRuleFieldSet) {
            fieldSet.insert(0, {
                xtype: 'label',
                html: '{s namespace="backend/swag_promotion/snippets" name="defineWhenPromotionWillApply"}This promotion will apply, if the following rules match.{/s}' + '<br><br>'
            });
        }

        if (arguments[2].isRebateRuleFieldSet) {
            me.applyRulesFieldSet = fieldSet;
            fieldSet.insert(0, {
                xtype: 'label',
                html: '{s namespace="backend/swag_promotion/snippets" name="forRelatedDiscountsApplies"}For product related discounts you can specify, which products of the basket the discount applies to.{/s}' + '<br><br>'
            });
        }

        return fieldSet;
    },

    /**
     * @Override
     */
    createAssociationComponent: function (type, model, store, association, baseRecord) {
        var me = this,
            component = me.callParent(arguments);

        component.margin = '0 0 10 0';

        if (component.title == 'blacklist-temp') {

            if (arguments[3].associationKey == 'doNotRunAfter') {
                component.title = '{s namespace="backend/swag_promotion/snippets" name="doNotRunAfterAndDescription"}<b>Do not run after: </b><br><br>Do not run this promotion, if one of the specified promotions has been applied before.{/s}';
            } else {
                component.title = '{s namespace="backend/swag_promotion/snippets" name="doNotRunAfterMeDescription"}<b>Do not allow later:</b><br><br>Do not allow any of the specified promotions, if this promotion has been applied.{/s}';
            }

            me.blacklistedPromotions.push({
                xtype: 'container',
                layout: 'hbox',
                items: [
                    {
                        xtype: 'label',
                        html: component.title,
                        width: 140
                    },
                    component
                ]
            });
        } else {
            me.associationItems.push({
                xtype: 'container',
                layout: 'hbox',
                items: [
                    {
                        xtype: 'label',
                        html: '<b>' + component.title + '</b>',
                        width: 140
                    },
                    component
                ]
            });
        }

        component.title = undefined;

        return component;
    },

    /**
     * @Override
     */
    createItems: function () {
        var me = this,
            associationFieldSet,
            promotionBlacklist,
            items = me.callParent(arguments);

        //remove all duplicate entries
        Object.keys(items).forEach(function (key) {
            if (items[key].title == undefined) {
                delete items[key];
            }
        });

        associationFieldSet = Ext.create('Ext.form.FieldSet', {
            collapsible: true,
            collapsed: true,
            items: [
                {
                    xtype: 'label',
                    html: '{s namespace="backend/swag_promotion/snippets" name="restrictPromotionGroupsOrShops"}If you want to restrict the promotion to certain customer groups / shops, you can make your selection here. If no restrictions are needed, just leave the fields empty.{/s}' + '<br><br>'
                }
            ].concat(me.associationItems),
            title: '{s namespace="backend/swag_promotion/snippets" name="discountSettingsFieldLabelRestrictions"}Restrictions{/s}'
        });

        promotionBlacklist = Ext.create('Ext.form.FieldSet', {
            collapsible: true,
            collapsed: true,
            items: [
                {
                    xtype: 'label',
                    html: '{s namespace="backend/swag_promotion/snippets" name="specifyPromotionIsBlocked"}Specify promotions, which block this promotion or are blocked by this promotion.{/s}' + '<br><br>'
                }
            ].concat(me.blacklistedPromotions),
            title: '{s namespace="backend/swag_promotion/snippets" name="discountSettingsFieldLabelPromotionExcludes"}Promotion excludes{/s}'
        });

        items = items.concat(associationFieldSet).concat(promotionBlacklist);

        return items;
    }
});
//{/block}
