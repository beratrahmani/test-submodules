{extends file='parent:frontend/index/index.tpl'}

{block name='frontend_index_navigation'}
    {if $b2bSuite}
        {action module=widgets controller=b2bsalesrepresentativebar}
    {/if}
    {$smarty.block.parent}
{/block}