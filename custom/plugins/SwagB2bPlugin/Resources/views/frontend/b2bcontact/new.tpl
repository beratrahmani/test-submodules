{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal.tpl"}

{block name="b2b_modal_base" prepend}
    {$modalSettings.navigation = false}
{/block}

{block name="b2b_modal_base_content_inner"}
    {include file="frontend/b2bcontact/_new.tpl"}
{/block}