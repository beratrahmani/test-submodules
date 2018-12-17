{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file='parent:frontend/b2bconfirm/index.tpl'}

{block name="b2b_confirm_modal_header"}
    {s name="ErrorModalHeader"}Error{/s}
{/block}

{block name="b2b_confirm_modal_body"}
    {$variables.message}
{/block}

{block name="b2b_confirm_modal_footer"}
    <button type="button" class="right btn b2b--confirm-action">
        {s name="ConfirmError"}Ok{/s}
    </button>
{/block}
