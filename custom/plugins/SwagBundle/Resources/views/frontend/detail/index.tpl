{extends file='parent:frontend/detail/index.tpl'}

{block name='frontend_detail_index_bundle'}
    {$smarty.block.parent}

    {block name='frontend_detail_swag_bundle_index_error'}
        {if $bundleMessage === 'notAvailable'}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name='DetailBundleNotAvailableError' namespace='frontend/detail/bundle'}{/s}"}
        {/if}
    {/block}

    {block name='frontend_detail_swag_bundle_index_bundle'}
        {include file='frontend/swag_bundle/index/bundle.tpl'}
    {/block}
{/block}

{block name='frontend_detail_tabs'}
    {$smarty.block.parent}

    {block name='frontend_detail_swag_bundle_tabs'}
        {include file='frontend/swag_bundle/index/tabs.tpl'}
    {/block}
{/block}
