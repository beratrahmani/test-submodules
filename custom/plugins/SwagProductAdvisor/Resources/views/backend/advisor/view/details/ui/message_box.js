//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/ui/message-box"}
Ext.define('Shopware.apps.Advisor.view.details.ui.MessageBox', {
    extend: 'Enlight.app.Window',
    alias: 'widget.advisor-details-ui-Message-Box',

    closable: false,
    maximizable: false,
    minimizable: false,
    resizable: false,
    modal: true,

    width: 500,
    height: null,
    bodyPadding: 15,

    okButtonText: '{s name="message_box_ok_button"}OK{/s}',
    yesButtonText: '{s name="message_box_yes_button"}Yes{/s}',
    noButtonText: '{s name="message_box_no_button"}No{/s}',
    cancelButtonText: '{s name="message_box_cancel_button"}Cancel{/s}',

    title: '{s name="message_box_default_title"}Default title{/s}',
    message: '{s name="message_box_default_message"}Default message{/s}',

    buttonClasses: {
        primary: 'primary',
        secondary: 'secondary'
    },

    OK : 1,
    YES : 2,
    NO : 3,
    CANCEL : 4,
    YESNO : 5,
    YESNOCANCEL : 6,
    OKCANCEL : 7,

    RESULTS: {
        OK: 'ok',
        YES: 'yes',
        NO: 'no',
        CANCEL: 'cancel'
    },

    /**
     * the init method
     */
    initComponent: function () {
        var me = this;

        me.items = [ me.createMessage() ];

        me.createChoiceButtons();

        me.callParent(arguments);
    },

    /**
     * @overwrite
     *
     * int from our configuration.
     * Please call show like:
     *      yourMessageBox.show({ buttons: messageBox.YESNOCANCEL })
     *
     * @param { { buttons: int } | * }  buttonConfiguration
     */
    show: function (buttonConfiguration) {
        var me = this;

        me.callParent();

        me.addDocked(me.createBBar(buttonConfiguration.buttons));
    },

    /**
     * @returns { Ext.container.Container }
     */
    createMessage: function () {
        var me = this;

        return Ext.create('Ext.container.Container',{
            html: me.message
        });
    },

    /**
     * creates the buttons
     */
    createChoiceButtons: function () {
        var me = this;

        me.OK_BTN = me.createOkButton();
        me.YES_BTN = me.createYesButton();
        me.NO_BTN = me.createNoButton();
        me.CANCEL_BTN = me.createCancelButton();
    },

    /**
     * @returns { Ext.button.Button | * }
     */
    createOkButton: function () {
        var me = this;

        return me.createDefaultButton(
            me.RESULTS.OK,
            me.okButtonText,
            me.buttonClasses.primary
        );
    },

    /**
     * @returns { Ext.button.Button | * }
     */
    createYesButton: function () {
        var me = this;

        return me.createDefaultButton(
            me.RESULTS.YES,
            me.yesButtonText,
            me.buttonClasses.primary
        );
    },

    /**
     * @returns { Ext.button.Button | * }
     */
    createNoButton: function () {
        var me = this;

        return me.createDefaultButton(
            me.RESULTS.NO,
            me.noButtonText,
            me.buttonClasses.secondary
        );
    },

    /**
     * @returns { Ext.button.Button | * }
     */
    createCancelButton: function () {
        var me = this;

        return  me.createDefaultButton(
            me.RESULTS.CANCEL,
            me.cancelButtonText,
            me.buttonClasses.secondary
        );
    },

    /**
     * @param btnResult
     * @param text
     * @param btnCls
     *
     * @returns { Ext.button.Button }
     */
    createDefaultButton: function (btnResult, text, btnCls) {
        var me = this;

        return Ext.create('Ext.button.Button', {
            btnResult: btnResult,
            text: text,
            cls: btnCls,
            handler: Ext.bind(me.buttonHandler, me)
        });
    },

    /**
     * @param { Ext.button.Button } btn
     */
    buttonHandler: function (btn) {
        var me = this;

        me.callBack(btn.btnResult);
        me.fireEvent('advisor_message_box_choice_selected', me, btn.btnResult);
        me.destroy();
    },

    /**
     * @param { string } buttonConfiguration
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createBBar: function (buttonConfiguration) {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            dock: 'bottom',
            cls: 'shopware-toolbar',
            items: me.getButtonConfiguration(buttonConfiguration)
        });
    },

    /**
     * @param buttonConfiguration
     *
     * @returns { Array | * }
     */
    getButtonConfiguration: function (buttonConfiguration) {
        var me = this;

        switch (buttonConfiguration) {
            case me.OK:
                return me.okConfig();
            case me.YES:
                return me.yesConfig();
            case me.NO:
                return me.noConfig();
            case me.CANCEL:
                return me.cancelConfig();
            case me.YESNO:
                return me.yesNoConfig();
            case me.YESNOCANCEL:
                return me.yesNoCancelConfig();
            case me.OKCANCEL:
                return me.okCancelConfig();
            default:
                return me.okConfig();
        }
    },

    /**
     * @returns { Array }
     */
    okConfig: function () {
        var me = this;

        return [
            '->',
            me.OK_BTN,
            '->'
        ];
    },

    /**
     * @returns { Array }
     */
    yesConfig: function () {
        var me = this;

        return [
            '->',
            me.YES_BTN,
            '->'
        ];
    },

    /**
     * @returns { Array }
     */
    noConfig: function () {
        var me = this;

        return [
            '->',
            me.NO_BTN,
            '->'
        ];
    },

    /**
     * @returns { Array }
     */
    cancelConfig: function () {
        var me = this;

        return [
            '->',
            me.CANCEL_BTN,
            '->'
        ]
    },

    /**
     * @returns { Array }
     */
    yesNoConfig: function () {
        var me = this;

        return [
            '->',
            me.YES_BTN,
            '->',
            me.NO_BTN,
            '->'
        ];
    },

    /**
     * @returns { Array }
     */
    yesNoCancelConfig: function () {
        var me = this;

        return [
            '->',
            me.NO_BTN,
            '->',
            me.CANCEL_BTN,
            me.YES_BTN
        ];
    },

    /**
     * @returns { Array }
     */
    okCancelConfig: function () {
        var me = this;

        return [
            '->',
            me.OK_BTN,
            '->',
            me.CANCEL_BTN,
            '->'
        ];
    },

    /**
     * @overwrite
     */
    destroy: function () {
        var me = this;
        me.callParent(arguments);
    },

    /**
     * This is your method to overwrite if you don't want to use the event.
     * you get the button result like below:
     *
     *  ok,
     *  no,
     *  yes,
     *  cancel
     *
     * @template
     * @param { string } btnResult
     */
    callBack: function (btnResult) {
        // DO STUFF
    }
});
//{/block}