{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/index/index.tpl"}

{* B2B Top Navigation *}
{block name="frontend_index_content_top"}
    {* B2B Account Header *}
    {include file="frontend/_base/topbar.tpl"}

{/block}

{* B2b Account Main Content *}
{block name="frontend_index_content"}
    <div class="b2b--plugin content--wrapper">
        {block name="frontend_index_content_b2b"}{/block}
    </div>
{/block}
