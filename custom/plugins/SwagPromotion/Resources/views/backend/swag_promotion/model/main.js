// {block name="backend/swag_promotion/model/main"}
Ext.define('Shopware.apps.SwagPromotion.model.Main', {
    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            controller: 'SwagPromotion',
            detail: 'Shopware.apps.SwagPromotion.view.detail.Container'
        };
    },

    fields: [
        // {block name="backend/swag_promotion/model/main/fields"}{/block}
        { name: 'applyRules', type: 'string', useNull: true },
        { name: 'amount', type: 'float', useNull: false },
        { name: 'step', type: 'int', useNull: true },
        { name: 'shippingFree', type: 'boolean', useNull: false },
        { name: 'maxQuantity', type: 'int', useNull: true },
        { name: 'type', type: 'string', useNull: false },
        { name: 'stackMode', type: 'string', useNull: false },
        { name: 'id', type: 'int', useNull: true },
        { name: 'name', type: 'string', useNull: false },
        { name: 'number', type: 'string', useNull: false },
        { name: 'active', type: 'boolean', useNull: false },
        { name: 'priority', type: 'int', useNull: false },
        { name: 'description', type: 'string', useNull: false },
        { name: 'maxUsage', type: 'int', useNull: false },
        { name: 'stopProcessing', type: 'boolean', useNull: false },
        { name: 'validFrom', type: 'date', dateFormat: 'Y-m-d' },
        { name: 'timeFrom', type: 'date', useNull: true, dateFormat: 'H:i' },
        { name: 'validTo', type: 'date', dateFormat: 'Y-m-d' },
        { name: 'timeTo', type: 'date', useNull: true, dateFormat: 'H:i' },
        { name: 'orders', type: 'int' },
        { name: 'turnover', type: 'float' },
        { name: 'voucherId', type: 'int', useNull: true },
        { name: 'noVouchers', type: 'boolean', useNull: false },
        { name: 'voucherButton', type: 'string', useNull: false },
        { name: 'detailDescription', type: 'string', useNull: false },
        { name: 'exclusive', type: 'boolean', useNull: false },
        { name: 'showBadge', type: 'boolean', useNull: false, defaultValue: true },
        { name: 'badgeText', type: 'string', useNull: true },
        { name: 'applyRulesFirst', type: 'boolean', defaultValue: false },
        { name: 'showHintInBasket', type: 'boolean', defaultValue: true }
    ],

    associations: [
        {
            relation: 'ManyToOne',
            field: 'voucherId',

            type: 'hasMany',
            model: 'Shopware.apps.SwagPromotion.model.Voucher',
            name: 'getVoucher',
            associationKey: 'voucher'
        },
        {
            relation: 'OneToOne',

            type: 'hasMany',
            model: 'Shopware.apps.SwagPromotion.model.Rules',
            name: 'getRules',
            associationKey: 'promotionRules'
        },
        {
            relation: 'OneToOne',

            type: 'hasMany',
            model: 'Shopware.apps.SwagPromotion.model.Discount',
            name: 'getDiscount',
            associationKey: 'discount'
        },
        {
            relation: 'ManyToMany',
            field: 'customerGroups',
            type: 'hasMany',
            model: 'Shopware.apps.SwagPromotion.model.CustomerGroup',
            name: 'getCustomerGroups',
            associationKey: 'customerGroups'
        },
        {
            relation: 'ManyToMany',
            field: 'shops',
            type: 'hasMany',
            model: 'Shopware.apps.SwagPromotion.model.Shop',
            name: 'getShops',
            associationKey: 'shops'
        },
        {
            relation: 'ManyToMany',
            field: 'doNotRunAfter',
            type: 'hasMany',
            model: 'Shopware.apps.SwagPromotion.model.PromotionAssociation',
            name: 'getDoNotRunAfter',
            associationKey: 'doNotRunAfter'
        },
        {
            relation: 'ManyToMany',
            field: 'doNotAllowLater',
            type: 'hasMany',
            model: 'Shopware.apps.SwagPromotion.model.PromotionAssociation',
            name: 'getDoNotAllowLater',
            associationKey: 'doNotAllowLater'
        },
        {
            relation: 'ManyToMany',
            field: 'freeGoodsArticle',
            type: 'hasMany',
            model: 'Shopware.apps.SwagPromotion.model.FreeGoodsArticle',
            name: 'getFreeGoodsArticle',
            associationKey: 'freeGoodsArticle'
        },
        {
            relation: 'ManyToOne',
            field: 'applyRules',

            type: 'hasMany',
            model: 'Shopware.apps.SwagRuleTree.model.Tree',
            name: 'getApplyRules',
            associationKey: '_applyrules'
        }
    ]
});
// {/block}
