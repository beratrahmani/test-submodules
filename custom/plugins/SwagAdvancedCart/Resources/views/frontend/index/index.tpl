{extends file="parent:frontend/index/index.tpl"}

{block name='frontend_index_header_javascript_jquery'}
    {block name='frontend_index_advanced_cart_header_javascript_jquery'}
        {include file="frontend/swag_advanced_cart/index/variables.tpl"}
    {/block}
    {$smarty.block.parent}
{/block}

{block name="frontend_index_body_classes"}
    {* Prevent 'is--minimal-header' class *}
    {if $sTarget === 'wishlist'}
        {$sTarget = 'account'}
    {/if}

    {$smarty.block.parent}
{/block}
