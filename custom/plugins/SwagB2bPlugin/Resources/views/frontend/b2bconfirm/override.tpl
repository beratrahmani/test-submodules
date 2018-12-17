{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file='parent:frontend/b2bconfirm/index.tpl'}

{block name="b2b_confirm_modal_header"}
    {s name="OverrideModalHeader"}Are you sure?{/s}
{/block}

{block name="b2b_confirm_modal_body"}
    {s name="OverrideModalMessage"}Are you sure you want to override the current items? All changes will be discarded.{/s}
{/block}

{block name="b2b_confirm_modal_footer"}
    <button type="button" class="right btn b2b--confirm-action">
        {s name="ConfirmOverride"}Yes, override!{/s}
    </button>
    <button type="button" class="right btn b2b--cancel-action">
        {s name="Cancel"}Cancel{/s}
    </button>
{/block}