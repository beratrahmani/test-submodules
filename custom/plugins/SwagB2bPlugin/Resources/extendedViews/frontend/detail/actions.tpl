{extends file="parent:frontend/detail/actions.tpl"}

{block name="frontend_detail_actions_notepad"}
    {if !$b2bSuite || !{b2b_acl_check controller=b2borderlist action=index}}
        {$smarty.block.parent}
    {/if}
{/block}