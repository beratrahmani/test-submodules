{extends file='parent:frontend/error/index.tpl'}

{block name='frontend_index_content'}
    {$smarty.block.parent}
    {if $b2bError}
        {include file="frontend/_includes/messages.tpl" type='error' b2bcontent=$b2bError}
    {/if}
{/block}