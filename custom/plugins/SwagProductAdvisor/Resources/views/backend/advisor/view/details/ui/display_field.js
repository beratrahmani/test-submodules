//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/ui/displayfield"}
Ext.define('Shopware.apps.Advisor.view.details.ui.DisplayField', {
    extend: 'Ext.form.FieldContainer',
    alias: 'widget.advisor-ui-display-field',
    layout: 'hbox',
    defaults: {
        layout: '100%'
    },

    snippets: {
        buttonText: '{s name="copy_button_text"}Copy{/s}'
    },

    /**
     * init the component
     */
    initComponent: function () {
        var me = this;

        me.displayField = me.createDisplayField();

        me.items = [
            me.displayField
        ];

        if (me.showCopyButton) {
            me.copyButton = me.createCopyButton();
            me.items.push(me.copyButton);
        }

        me.callParent(arguments);
    },

    /**
     * the afterRender method
     */
    afterRender: function () {
        var me = this;
        me.callParent(arguments);

        if (me.helpText) {
            me.createHelp();
        }
    },

    /**
     * create the display field
     *
     * @returns { Ext.form.field.Display }
     */
    createDisplayField: function () {
        var me = this;

        return Ext.create('Ext.form.field.Display', {
            style: {
                background: '#fff'
            },
            fieldStyle: {
                margin: '0 5px 0 5px',
                color: '#95a3ad'
            },
            cls: 'display-field-class',
            flex: 1,
            height: '20px',
            margin: '0',
            name: me.childName,
            allowBlank: true
        });
    },

    /**
     * create the Copy button
     *
     * @returns { Ext.button.Button }
     */
    createCopyButton: function () {
        var me = this;

        return Ext.create('Ext.button.Button', {
            cls: Ext.baseCSSPrefix + 'form-mediamanager-btn small secondary',
            iconCls: 'sprite-blue-document-copy',
            text: me.snippets.buttonText,
            margin: '0 0 0 3px',
            padding: '2px 5px',
            handler: function () {
                me.onButtonClick();
            }
        });
    },

    /**
     * this is the eventHandler of the CopyButton
     *
     * Select the text in the DisplayField an copy to Clipboard
     */
    onButtonClick: function () {
        var me = this,
            div = me.displayField.getEl().down('.x-form-display-field', true),
            range;

        if (!div) {
            return;
        }

        if (document.selection) {
            range = document.body.createTextRange();
            range.moveToElementText(div);
            range.select();
        } else if (window.getSelection) {
            range = document.createRange();
            range.selectNode(div);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
        }

        document.execCommand('copy');
    },

    /**
     * create the helpText
     *
     * @returns { * }
     */
    createHelp: function () {
        var me = this,
            helpIcon = new Ext.Element(document.createElement('span')),
            row = new Ext.Element(document.createElement('td'));

        row.set({ width: 24, valign: 'top' });
        helpIcon.set({ cls: Ext.baseCSSPrefix + 'form-help-icon' });
        helpIcon.appendTo(row);

        Ext.tip.QuickTipManager.register({
            target: helpIcon,
            cls: Ext.baseCSSPrefix + 'form-tooltip',
            title: (me.helpTitle) ? me.helpTitle : '',
            text: me.helpText,
            width: (me.helpWidth) ? me.helpWidth : 225,
            anchorToTarget: true,
            anchor: 'right',
            anchorSize: {
                width: 24,
                height: 24
            },
            defaultAlign: 'tr',
            showDelay: me.helpTooltipDelay,
            dismissDelay: me.helpTooltipDismissDelay
        });

        row.appendTo(this.inputRow);

        this.helpIconEl = helpIcon;
        return helpIcon;
    }
});
//{/block}