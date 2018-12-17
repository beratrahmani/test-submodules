{extends file='parent:frontend/listing/index.tpl'}

{block name='frontend_listing_index_topseller'}{/block}

{block name='frontend_listing_index_layout_variables'}
    {$smarty.block.parent}

    {block name='frontend_listing_swag_bundle_index_layout_variables'}
        {include file='frontend/swag_bundle/bundle/variables.tpl' scope='parent'}
    {/block}
{/block}
