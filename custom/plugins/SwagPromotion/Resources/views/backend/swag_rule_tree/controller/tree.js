
//{namespace name="backend/swag_promotion/main"}
//{block name="backend/swag_rule_tree/controller/tree"}
Ext.define('Shopware.apps.SwagTreeRule.controller.Tree', {
    extend: 'Enlight.app.Controller',

    treeStore: undefined,
    treePanel: undefined,

    init: function (value) {
        var me = this;
        if (value) {
            me.buildTree(value);
        }
    },

    buildTree: function (value) {
        var me = this;

        value = JSON.parse(value);

        var node = me.treeStore.getById('root');

        node.removeAll();

        var firstElement = value.hasOwnProperty('and') ? 'and' : 'or';
        node.data.text = me.getText(firstElement, {});
        node.raw.rule = firstElement;
        node.raw.ruleNormalized = me.normalize(firstElement);

        me.recursiveBuildTree(value[firstElement], node);
    },

    recursiveBuildTree: function (object, parentNode) {
        var me = this,
            data,
            newNode,
            ruleConfig,
            isParentNode,
            property;

        for (property in object) {
            if (!object.hasOwnProperty(property)) {
                continue;
            }
            isParentNode = me.normalize(property) == 'and' || me.normalize(property) == 'or';

            ruleConfig = isParentNode ? {} : object[property];

            data = {
                expanded: true,
                leaf: !isParentNode,
                text: me.getText(property, ruleConfig),
                rule: property,
                ruleNormalized: me.normalize(property),
                ruleConfig: ruleConfig
            };

            newNode = parentNode.appendChild(data);
            if (isParentNode) {
                me.recursiveBuildTree(object[property], newNode);
            }
        }
    },

    getTreeJson: function () {
        var me = this,
            result;

        var node = me.treeStore.getById('root');

        node.raw.rule = me.normalize(node.raw.rule);
        result = me.recursiveReadTree(node);

        return JSON.stringify(result);
    },

    recursiveReadTree: function (node) {
        var me = this,
            result = {},
            children = {},
            rule = node.raw.rule,
            ruleConfig = node.raw.ruleConfig;

        if (rule == undefined) {
            throw "Rule cannot be undefined";
        }

        if (node.get('leaf')) {
            result[rule] = ruleConfig;
            return result;
        }

        Ext.each(node.childNodes, function (child) {
            children = Ext.merge(children, me.recursiveReadTree(child));
        });

        result[rule] = children;

        return result;
    },

    getText: function (rule, ruleConfig) {
        var me = this,
            operator,
            values;

        rule = me.normalize(rule);

        if (rule == 'or') {
            return '{s name=orLong}If one or more of these is true{/s}'
        }
        if (rule == 'and') {
            return '{s name=andLong}If all of these is true{/s}'
        }
        if (rule == 'true') {
            return '{s name=ruleTrue}Always true{/s}'
        }
        if (rule == 'stream') {
            return '{s name=streamRule}Is in product stream{/s}'
        }

        values = '"' + ruleConfig[2] + '"';
        if (Array.isArray(values)) {
            values = '(' + values.join() + ')';
        }

        switch (ruleConfig[1]) {
            case '=':
            case '==':
                operator = '{s name=equals}equals{/s}';
                break;
            case 'in':
                operator = '{s name=oneOf}is one of{/s}';
                break;
            case 'notin':
                operator = '{s name=notOneOf}is NOT one of{/s}';
                break;
            case '>':
                operator = '{s name=bigger}is bigger then{/s}';
                break;
            case '<':
                operator = '{s name=smaller}is smaller then{/s}';
                break;
            case '<=':
                operator = '{s name=smallerEquals}is smaller then or equal to{/s}';
                break;
            case '>=':
                operator = '{s name=biggerEquals}is bigger then or equal to{/s}';
                break;
            case '!=':
            case '<>':
                operator = '{s name=unequalTo}is unequal to{/s}';
                break;
            case 'contains':
                operator = '{s name=contains}contains{/s}';
                break;
            case 'notcontains':
                operator = '{s name=notContains}does NOT contain{/s}';
                break;
            case 'istrue':
                operator = '{s name=true}is true{/s}';
                values = '';
                break;
            case 'isfalse':
                operator = '{s name=false}is false{/s}';
                values = '';
                break;
        }

        return rule + ': ' + 'if ' + ruleConfig[0] + ' ' + operator + ' ' + values;

    },

    normalize: function (string) {
        if (string) {
            return string.replace(/[^a-zA-Z]/gi, '');
        }
        return '';
    },

    reconfigureNode: function (node, ruleType, ruleConfig) {
        var me = this,
            parentNode,
            currentNode,
            normalizedRule = me.normalize(ruleType),
            children = [],
            leaf = !(normalizedRule == 'and' || normalizedRule == 'or');

        // if a node becomes a leaf, move children up
        if (!node.get('leaf') && leaf) {
            parentNode = node.parentNode;
            currentNode = parentNode.indexOf(node);
            node.eachChild(function (child) {
                children.push(child);
            });
            for (var i = 0; i < children.length; i++) {
                parentNode.insertChild(currentNode, children[i]);
            }
        }

        node.set('leaf', leaf);

        node.raw.ruleNormalized = normalizedRule;
        node.raw.rule = ruleType + Math.random();
        node.raw.ruleConfig = ruleConfig;

        node.set('text', me.getText(ruleType, ruleConfig));
    }
});
//{/block}
