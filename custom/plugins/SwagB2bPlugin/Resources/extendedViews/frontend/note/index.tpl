{extends file="parent:frontend/note/index.tpl"}

{block name="frontend_index_sidebar"}
    {if !$b2bSuite}
        {$smarty.block.parent}
    {/if}
{/block}