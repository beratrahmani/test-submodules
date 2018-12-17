{extends file='parent:frontend/detail/content/buy_container.tpl'}

{block name='frontend_detail_index_actions'}
    {$smarty.block.parent}
    {block name='frontend_detail_index_actions_promotion'}
        {include file='frontend/swag_promotion/detail/actions.tpl'}
    {/block}
{/block}
