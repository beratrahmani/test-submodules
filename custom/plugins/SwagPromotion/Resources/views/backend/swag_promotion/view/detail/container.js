// {namespace name="backend/swag_promotion/snippets"}
// {block name="backend/promotion/view/detail/container"}
Ext.define('Shopware.apps.SwagPromotion.view.detail.Container', {
    extend: 'Shopware.model.Container',
    padding: 20,
    alias: 'widget.swag-promotion-main-window',
    layout: 'anchor',
    defaults: {
        anchor: '100%'
    },

    // use InitComponent, getForm and getFields for the TranslationPlugin
    initComponent: function () {
        var me = this;

        me.callParent(arguments);
        me.on('afterrender', function () {
            Ext.Function.defer(function () {
                me.enableValidFields();
            }, 500);
        });
    },

    /**
     * @returns { { controller: string, associations: string[], fieldSets: *[] } }
     */
    configure: function () {
        var me = this;

        me.freeGoodsArticleComponent = [];

        return {
            controller: 'SwagPromotion',
            associations: [
                'freeGoodsArticle'
            ],
            fieldSets: [
                {
                    title: '{s name="mainBasicSettings"}Basic settings{/s}',
                    layout: 'column',
                    fields: {
                        type: me.createTypeCombo,
                        name: {
                            fieldLabel: '{s name="discountSettingsFieldLabelName"}Name of the Promotion{/s}',
                            helpText: '{s name="showInCustomerCart"}The name will be shown to the customer in the cart and in the order details{/s}',
                            translatable: true,
                            columnWidth: 0.5
                        },
                        showBadge: {
                            fieldLabel: '{s name="showActionBadge"}Show badge in listing{/s}',
                            helpText: '{s name="showActionBadgeHelpText"}If set, the products in the listing are provided with a badge, if the products are valid for this promotion.{/s}',
                            columnWidth: 0.5
                        },
                        showHintInBasket: {
                            fieldLabel: '{s name="fieldLabelShowHintInBasket"}Show hint in basket{/s}',
                            helpText: '{s name="helpTextShowHintInBasket"}If this option is activated, the customer will receive a notification in the shopping cart that indicates the conditions of the promotion have not yet been fulfilled.{/s}',
                            columnWidth: 0.5
                        },
                        active: {
                            columnWidth: 0.5,
                            fieldLabel: '{s name="discountSettingsFieldLabelActive"}Active{/s}',
                            helpText: '{s name="onlyActivePromotions"}The promotion can only be used by your customers in the store if it is active.{/s}'
                        },
                        number: {
                            columnWidth: 0.5,
                            fieldLabel: '{s name="discountSettingsFieldLabelNumber"}Order number{/s}'
                        },
                        badgeText: {
                            fieldLabel: '{s name="badgeText"}Badge text{/s}',
                            helpText: '{s name="badgeTextHelpText"}If set, this text will be shown on the product badge in the listing{/s}',
                            translatable: true,
                            columnWidth: 0.5
                        }
                    }
                },
                {
                    title: '{s name="mainPromotionConfig"}Promotion configuration{/s}',
                    fields: {
                        maxUsage: {
                            fieldLabel: '{s name="discountSettingsFieldLabelMaxUsage"}Max usage per user{/s}',
                            helpText: '{s name="maxUsePromotion"}How often may a customer use this promotion? 0=infinite{/s}',
                            minValue: 0,
                            columnWidth: 0.5
                        },
                        exclusive: {
                            fieldLabel: '{s name="discountSettingsFieldLabelExclusive"}Use only this promotion{/s}',
                            helpText: '{s name="discountSettingsFieldLabelExclusiveHelpText"}All other promotions will be excluded.{/s}',
                            columnWidth: 0.5
                        },
                        noVouchers: {
                            columnWidth: 0.5,
                            fieldLabel: '{s name="discountSettingsNoVouchers"}Allow no vouchers{/s}',
                            helpText: '{s name="discountSettingsNoVouchersHelpText"}If checked only the voucher for activation [used in the field voucher] will be valid and no other vouchers can be used within the order. If not checked, only one voucher can be added additionally.{/s}'
                        },
                        applyRulesFirst: {
                            columnWidth: 0.5,
                            fieldLabel: '{s name=applyRulesFirstLabel}Refer promotion rules to product rules{/s}',
                            helpText: '{s name=applyRulesFirstHelpText}If checked, the product rules of the promotion will be considered first<br><br>Example:<br>You give 10% discount on products of category A, but only if the cart total amount is more than 100€.<br><br><b>Unchecked</b>: The promotion will apply if the cart total amount is more than 100€ and you have at least one item of category A in the cart, even if the total amount of the cart items from category A is below 100€<br><br><b>Checked</b>: The promotion will only apply if you have items from category A worth more than 100€ in the cart.{/s}'
                        }
                    }
                },
                {
                    isStackModeFieldSet: true,
                    identifier: 'stackModeFieldSet',
                    layout: 'column',
                    title: '{s name="mainStackModeFieldSet"}Discount assignment{/s}',
                    fields: {
                        stackMode: me.createStackModeCombo,
                        step: {
                            fieldLabel: '{s name="discountSettingsFieldLabelStep"}Scaling{/s}',
                            minValue: 1,
                            lableWidth: 130,
                            columnWidth: 0.483,
                            identifier: 'step',
                            helpText: '{s name="discountBiggerThanDescription"}The scaling defines how much products <b>P</b> the user has to buy to activate this promotion.{/s}'
                        },
                        maxQuantity: {
                            minValue: 0,
                            columnWidth: 0.5,
                            lableWidth: 130,
                            fieldLabel: '{s name="discountSettingsFieldLabelMaxQuantity"}Max usage per cart{/s}',
                            identifier: 'maxQuantity',
                            helpText: '{s name="limitTheDiscounts"}Define how often this promotion can be used in a basket. If no entry was made the promotion will be true for all valid products{/s}'
                        }
                    }
                },
                {
                    title: '{s name="mainPromotionShortDescription"}Short description{/s}',
                    layout: 'fit',
                    padding: '10 0 10 20',
                    fields: {
                        description: {
                            fieldLabel: '',
                            helpText: '{s name="descriptionInListingAndDetail"}This description is required to point out the promotions on detail pages{/s}',
                            xtype: 'tinymce',
                            translatable: true,
                            supportText: me.getDescriptionVars()
                        }
                    }
                },
                {
                    title: '{s name="mainPromotionDetailDescription"}Detailed description{/s}',
                    layout: 'fit',
                    padding: '10 0 10 20',
                    fields: {
                        detailDescription: {
                            fieldLabel: '',
                            helpText: '{s name="mainPromotionDetailDescriptionSupport"}This description is shown in a modal box, or off canvas on mobile devices. It can be used for disclaimer texts, etc{/s}',
                            xtype: 'tinymce',
                            translatable: true,
                            supportText: me.getDescriptionVars()
                        }
                    }
                },
                {
                    isVoucherFieldSet: true,
                    layout: 'anchor',
                    padding: '5px 0 5px 20px',
                    title: '{s name="mainVoucherFieldSetName"}Voucher{/s}',
                    fields: {
                        voucherId: {
                            xtype: 'combobox',
                            labelWidth: 130,
                            anchor: '100%',
                            fieldLabel: '{s name="discountSettingsFieldLabelVoucher"}Activate promotion with existing voucher{/s}',
                            displayField: 'description',
                            valueField: 'id',
                            helpText: '{s name="discountSettingsFieldLabelVoucherSupportText"}Would you like to link a voucher to this promotion? In this case the customer needs to use the voucher in the basket to activate the promotion.{/s}',
                            pageSize: 25,
                            minChars: 0
                        }
                    }
                }
            ]
        };
    },

    /**
     * @override
     */
    createAssociationComponent: function (type, model, store, association, baseRecord) {
        var me = this,
            component = me.callParent(arguments);

        component.anchor = '100%';

        if (component.alias.indexOf('widget.swag-promotion-free-goods') !== -1) {
            var freeGoodHeadline = Ext.create('Ext.container.Container', {
                flex: 1,
                anchor: '100%',
                html: '<b>{s name="freeGoods"}Free goods product{/s}:</b>',
                style: {
                    padding: '0, 20px, 10px, 0',
                    marginTop: '20px'
                }
            });
            me.freeGoodsArticleComponent.push({
                xtype: 'container',
                flex: 1,
                layout: 'anchor',
                identifier: 'freeGoodsArticle',
                columnWidth: 1,
                width: '100%',
                items: [
                    freeGoodHeadline,
                    component
                ]
            });
        }

        component.title = undefined;

        return component;
    },

    /**
     * @override
     */
    createItems: function () {
        var me = this,
            timeSettingFieldSet,
            advancedSettingFieldSet,
            discountFieldSet,
            reorderedItems = [],
            items = me.callParent(arguments);

        // remove all duplicate entries
        Object.keys(items).forEach(function (key) {
            if (!Ext.isDefined(items[key].title)) {
                delete items[key];
            }
        });

        timeSettingFieldSet = Ext.create('Ext.form.FieldSet', {
            collapsible: true,
            collapsed: true,
            layout: 'anchor',
            title: '{s name="mainTimeBasedActivation"}Time based promotion activation{/s}',
            items: [
                {
                    layout: 'column',
                    xtype: 'container',
                    items: [
                        {
                            xtype: 'datefield',
                            columnWidth: 0.5,
                            padding: '0, 20, 0, 0',
                            fieldLabel: '{s name="discountSettingsFieldLabelValidFrom"}Valid from{/s}',
                            supportText: '{s name="firstDayPromotionIsValid"}First day the promotion is valid{/s}',
                            name: 'validFrom',
                            format: 'Y-m-d',
                            submitFormat: 'Y-m-d',
                            width: '100%'
                        },
                        {
                            xtype: 'timefield',
                            columnWidth: 0.5,
                            padding: '0, 20, 0, 0',
                            supportText: '{s name="discountSettingsSupportTextFromTime"}Time the promotion is valid{/s}',
                            fieldLabel: '{s name="discountSettingsFieldLabelFromTime"}From time{/s}',
                            name: 'timeFrom',
                            format: 'H:i',
                            submitFormat: 'H:i',
                            width: '100%'
                        },
                        {
                            xtype: 'datefield',
                            columnWidth: 0.5,
                            padding: '0, 20, 0, 0',
                            fieldLabel: '{s name="discountSettingsFieldLabelValidTo"}Valid to{/s}',
                            supportText: '{s name="lastDayPromotionIsValid"}Last day and hour the promotion is valid{/s}',
                            name: 'validTo',
                            format: 'Y-m-d',
                            submitFormat: 'Y-m-d',
                            width: '100%'
                        },
                        {
                            xtype: 'timefield',
                            columnWidth: 0.5,
                            padding: '0, 20, 0, 0',
                            supportText: '{s name="discountSettingsSupportTextToTime"}Time Promotion is terminated{/s}',
                            fieldLabel: '{s name="discountSettingsFieldLabelToTime"}To time{/s}',
                            name: 'timeTo',
                            format: 'H:i',
                            submitFormat: 'H:i',
                            width: '100%'
                        }
                    ]
                }
            ]
        });

        discountFieldSet = Ext.create('Ext.form.FieldSet', {
            layout: 'anchor',
            title: '{s name="mainDiscountConfig"}Discount{/s}',
            identifier: 'discountFieldSet',
            items: [
                {
                    layout: 'column',
                    xtype: 'container',
                    identifier: 'discountFieldSetInner',
                    items: [
                        {
                            xtype: 'numberfield',
                            identifier: 'amount',
                            columnWidth: 0.5,
                            padding: '0, 20, 0, 0',
                            labelWidth: 130,
                            fieldLabel: '{s name="discountSettingsFieldLabelGetForFree"}Buy X{/s}',
                            helpText: '{s name="fieldLabelAmountHelpText"}This is the percental or absolute value that will be deducted based on the Discount Mode field.{/s}',
                            name: 'amount',
                            width: '100%'
                        },
                        {
                            xtype: 'checkbox',
                            columnWidth: 0.5,
                            labelWidth: 130,
                            identifier: 'shippingFree',
                            fieldLabel: '{s name="discountSettingsFieldLabelShippingFree"}Shipping free{/s}',
                            helpText: '{s name="notApplyShippingCosts"}Do not apply shipping costs{/s}',
                            name: 'shippingFree',
                            width: '100%',
                            inputValue: true,
                            uncheckedValue: false
                        },
                        {
                            xtype: 'container',
                            columnWidth: 1,
                            layout: 'hbox',
                            items: me.freeGoodsArticleComponent,
                            width: '100%'
                        }
                    ]
                }
            ]
        });

        advancedSettingFieldSet = Ext.create('Ext.form.FieldSet', {
            collapsible: true,
            collapsed: true,
            layout: 'column',
            title: '{s name="mainAdvancedSettings"}Advanced settings{/s}',
            items: [
                {
                    layout: 'column',
                    xtype: 'container',
                    items: [
                        {
                            xtype: 'checkbox',
                            columnWidth: 0.5,
                            labelWidth: 130,
                            padding: '0, 20, 0, 0',
                            fieldLabel: '{s name="discountSettingsFieldLabelStopProcessing"}Exclude promotions with lower priority{/s}',
                            helpText: '{s name="doNotProcessMoreRules"}Do not apply other promotions when this mode is activated{/s}',
                            name: 'stopProcessing',
                            width: '100%',
                            inputValue: true,
                            uncheckedValue: false,
                            listeners: {
                                change: function (el) {
                                    var val = el.value,
                                        originalValue = me.record.get('stopProcessing');

                                    if (val === true && originalValue === false) {
                                        Ext.Msg.show({
                                            title: '{s name=setPrioListenerConfirmMessageTitle}Caution{/s}',
                                            msg: '{s name=setPrioListenerConfirmMessageBody}If you activate this option all promotions with a lower priority will be excluded from the basket. Check all necessary promotions for its priority!{/s}',
                                            buttons: Ext.Msg.OKCANCEL,
                                            fn: function (btn) {
                                                if (btn === 'ok') {
                                                    Ext.Msg.hide();
                                                } else if (btn === 'cancel') {
                                                    el.setValue(false);
                                                    Ext.Msg.hide();
                                                }
                                            }
                                        });
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'numberfield',
                            columnWidth: 0.5,
                            labelWidth: 130,
                            fieldLabel: '{s name="discountSettingsFieldLabelPriority"}Priority of this promotion{/s}',
                            helpText: '{s name="promotionPriority"}Promotions with higher priority will be executed first{/s}',
                            name: 'priority',
                            width: '100%'
                        }
                    ]
                }
            ]
        });

        items = items.concat(discountFieldSet).concat(timeSettingFieldSet).concat(advancedSettingFieldSet);

        Object.keys(items).forEach(function (key) {
            if (items[key].identifier === 'discountFieldSet') {
                reorderedItems.push(items[key]);
                delete items[key];
            }
        });

        items.splice(1, 0, reorderedItems[0]);

        return items;
    },

    /**
     * Creates the combo box with the type selection
     *
     * @returns { Ext.form.field.ComboBox }
     */
    createTypeCombo: function () {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'type',
            fieldLabel: '{s name="promotionMode"}Promotion mode{/s}',
            labelWidth: 130,
            columnWidth: 1,
            anchor: '100%',
            displayField: 'name',
            helpText: '{s name="promotionModeHelpText"}Choose which type of discount this promotion should have.{/s}',
            valueField: 'type',
            editable: false,
            allowBlank: false,
            store: Ext.create('Ext.data.Store', {
                data: me.getTypeList(),
                fields: ['type', 'name']

            }),
            listeners: {
                select: function () {
                    me.enableValidFields();
                }
            }
        });
    },

    /**
     * Creates stack mode combo box
     *
     * @returns { Ext.form.field.ComboBox }
     */
    createStackModeCombo: function () {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'stackMode',
            fieldLabel: '{s name="mainStackModeLabel"}Discount available for{/s}',
            labelWidth: 130,
            anchor: '100%',
            columnWidth: 0.5,
            displayField: 'name',
            helpText: '{s name="WhenUsingStep"}This field defines which type of product must be bought to activate the promotion. Either a variant, a base product or any product.{/s}',
            valueField: 'type',
            editable: false,
            store: Ext.create('Ext.data.Store', {
                data: me.getStackModeList(),
                fields: ['type', 'name']

            }),
            listeners: {
                select: function () {
                    me.enableValidFields();
                }
            }
        });
    },

    /**
     * Returns a list of types for the type combo
     *
     * @returns { *[] }
     */
    getTypeList: function () {
        return [
            {
                type: 'basket.absolute',
                name: '{s name="basketAbsoluteDiscount"}Cart: Absolute discount{/s}'
            },
            {
                type: 'basket.percentage',
                name: '{s name="basketPercentDiscount"}Cart: Percentage discount{/s}'
            },
            {
                type: 'product.absolute',
                name: '{s name="productAbsoluteDiscount"}Products: Absolute discount{/s}'
            },
            {
                type: 'product.percentage',
                name: '{s name="productPercentDiscount"}Products: Percentage discount{/s}'
            },
            {
                type: 'product.buyxgetyfree',
                name: '{s name="buyxgetyfree"}Buy X get Y for free{/s}'
            },
            {
                type: 'product.freegoods',
                name: '{s name="freeGoodsDiscount"}Free goods{/s}'
            },
            {
                type: 'basket.shippingfree',
                name: '{s name="discountSettingsFieldLabelShippingFree"}Shipping free{/s}'
            }
        ];
    },

    /**
     * Returns a list of stack modes for the stack mode combo
     *
     * @returns { *[] }
     */
    getStackModeList: function () {
        return [
            {
                'type': 'detail',
                'name': '{s name="stackModeByVariant"}By Variant{/s}'
            },
            {
                'type': 'article',
                'name': '{s name="stackModeByBaseArticle"}By base article{/s}'
            },
            {
                'type': 'global',
                'name': '{s name="stackModeByAllArticle"}All article{/s}'
            }
        ];
    },

    /**
     * Handle the fields (enable, disable, show, hide)
     */
    enableValidFields: function () {
        var me = this,
            type = me.down('combo[name=type]'),
            stackMode = me.down('combo[name=stackMode]'),
            maxQuantity = me.down('numberfield[identifier=maxQuantity]'),
            step = me.down('numberfield[identifier=step]'),
            shippingFree = me.down('[identifier=shippingFree]'),
            amount = me.down('numberfield[identifier=amount]'),
            tabPanel = me.up('tabpanel'),
            freeGoodComponent = me.down('[identifier=freeGoodsArticle]'),
            stackModeFieldSet = me.down('[identifier=stackModeFieldSet]'),
            discountFieldSet = me.down('[identifier=discountFieldSetInner]'),
            discountFieldSetFieldSet = me.down('[identifier=discountFieldSet]'),
            applyRulesFirstField = me.down('[name=applyRulesFirst]'),
            productRuleComponent = {};

        var tab = tabPanel.down('tab[text={s name="promotionRuleTabTitle"}Promotion rule{/s}]');

        Object.keys(tab.card.items.items).forEach(function (key) {
            if (tab.card.items.items[key].identifier === 'productRuleFieldset') {
                productRuleComponent = tab.card.items.items[key];
            }
        });

        if (stackMode.getValue() === '') {
            stackMode.setValue('global');
        }

        discountFieldSetFieldSet.show();
        if (type.getValue() === 'basket.percentage' || type.getValue() === 'basket.absolute') {
            step.hide();
            stackModeFieldSet.hide();
            maxQuantity.hide();
            freeGoodComponent.hide();
            productRuleComponent.hide();
            amount.show();
            applyRulesFirstField.hide();
            discountFieldSet.doLayout();
        } else if (type.getValue() === 'basket.shippingfree') {
            step.hide();
            stackModeFieldSet.hide();
            maxQuantity.hide();
            amount.hide();
            freeGoodComponent.hide();
            applyRulesFirstField.hide();
            productRuleComponent.hide();
            shippingFree.setValue(true);
            discountFieldSetFieldSet.hide();
        } else if (type.getValue() === 'product.freegoods') {
            freeGoodComponent.show();
            productRuleComponent.show();
            amount.hide();
            applyRulesFirstField.show();
            discountFieldSet.doLayout();
        } else {
            freeGoodComponent.hide();
            productRuleComponent.show();
            amount.show();
            step.show();
            stackModeFieldSet.show();
            maxQuantity.show();
            applyRulesFirstField.show();
            discountFieldSet.doLayout();
        }

        if (type.getValue() === 'basket.percentage' || type.getValue() === 'product.percentage') {
            amount.setFieldLabel('{s name="fieldLabelAmountPercent"}Value %{/s}');
            step.setFieldLabel('{s name="fieldLabelStep"}Scaling{/s}');
            stackModeFieldSet.insert(step);
            discountFieldSet.remove(step, true);
            stackModeFieldSet.doLayout();
            discountFieldSet.doLayout();
            stackModeFieldSet.show();
        } else if (type.getValue() === 'product.buyxgetyfree') {
            amount.setFieldLabel('{s name="fieldLabelGetYForFree"}Get Y for free{/s}');
            amount.allowDecimals = false;
            step.setFieldLabel('{s name="fieldLabelBuyX"}Buy X{/s}');
            step['columnWidth'] = 0.5;
            shippingFree['columnWidth'] = 0.483;
            discountFieldSet.insert(1, step);
            stackModeFieldSet.remove(step, true);
            discountFieldSet.doLayout();
            stackModeFieldSet.doLayout();
            stackModeFieldSet.hide();
        } else if (type.getValue() === 'product.freegoods') {
            shippingFree['columnWidth'] = 0.483;
            step.setFieldLabel('{s name="fieldLabelStep"}Scaling{/s}');
            amount.setFieldLabel('{s name="fieldLabelAmount"}Value{/s}');
            step['columnWidth'] = 0.483;
            stackModeFieldSet.insert(step);
            discountFieldSet.remove(step, true);
            stackModeFieldSet.doLayout();
            discountFieldSet.doLayout();
            stackModeFieldSet.show();
        } else {
            step.setFieldLabel('{s name="fieldLabelStep"}Scaling{/s}');
            amount.setFieldLabel('{s name="fieldLabelAmount"}Value{/s}');
            step['columnWidth'] = 0.483;
            shippingFree['columnWidth'] = 0.5;
            stackModeFieldSet.insert(step);
            discountFieldSet.remove(step, true);
            stackModeFieldSet.doLayout();
            discountFieldSet.doLayout();
            stackModeFieldSet.show();
        }
    },

    /**
     * @returns { string }
     */
    getDescriptionVars: function () {
        var varString = '{s name="availableDescriptionVars"}Available Variables{/s}<br>{literal}',
            availableVars = {
                'promotion->name': '{/literal}{s name="discountSettingsFieldLabelName"}Name of the Promotion{/s}{literal}',
                'promotion->amount': '{/literal}{s name="fieldLabelAmount"}Value{/s}{literal}',
                'promotion->validFrom': '{/literal}{s name="discountSettingsFieldLabelValidFrom"}Valid From{/s}{literal}',
                'promotion->validTo': '{/literal}{s name="discountSettingsFieldLabelValidTo"}Valid to{/s}{literal}',
                'promotion->maxUsage': '{/literal}{s name="discountSettingsFieldLabelMaxUsage"}Max usage per user{/s}{literal}'
            },
            count = 1;

        Object.keys(availableVars).forEach(function (key) {
            varString = varString + '{$' + key + '} => ' + availableVars[key] + ', ';
            if (count === 4) {
                varString = varString + '<br>';
                count = 1;
            } else {
                count++;
            }
        });

        return varString.substr(0, varString.length - 2) + '{/literal}';
    }
});
// {/block}
