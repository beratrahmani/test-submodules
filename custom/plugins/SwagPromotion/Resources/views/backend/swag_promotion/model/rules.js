//{block name="backend/swag_promotion/model/rules"}
Ext.define('Shopware.apps.SwagPromotion.model.Rules', {

    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            detail: 'Shopware.apps.SwagPromotion.view.detail.Rules'
        }
    },

    fields: [
        //{block name="backend/swag_promotion/model/rules/fields"}{/block}
        { name: 'applyRules', type: 'string', useNull: false },
        { name: 'rules', type: 'string', useNull: false }
    ],

    associations: [
        {
            relation: 'ManyToOne',
            field: 'rules',

            type: 'hasMany',
            model: 'Shopware.apps.SwagRuleTree.model.Tree',
            name: 'getPromotionRules',
            associationKey: '_rules'
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
            relation: 'ManyToOne',
            field: 'applyRules',

            type: 'hasMany',
            model: 'Shopware.apps.SwagRuleTree.model.Tree',
            name: 'getApplyRules',
            associationKey: '_applyrules'
        }
    ]
});
//{/block}
