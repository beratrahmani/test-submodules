{extends file="parent:frontend/index/index.tpl"}
{namespace name="frontend/plugins/swag_advanced_cart/plugin"}

{* Breadcrumb *}
{block name='frontend_index_start'}
    {assign var='sBreadcrumb' value=[['name'=>"{s namespace="frontend/account/index" name='AccountTitle'}{/s}", 'link' =>{url controller='account' action='index'}]]}
    {$sBreadcrumb[] = ["name"=>"{s name="WishlistsTitle" namespace='frontend/plugins/swag_advanced_cart/content_right'}{/s}", "link"=>{url controller="wishlist" action="index"}]}

    {block name='frontend_index_advanced_cart_start'}
        {include file="frontend/wishlist/index/start.tpl"}
    {/block}
    {$smarty.block.parent}
{/block}

{* sidebar *}
{block name="frontend_index_left_categories"}
    {block name="frontend_index_left_categories_headline"}{/block}
    {block name='frontend_index_left_categories_inner'}{/block}
    {block name="frontend_account_sidebar"}
        {include file="frontend/account/sidebar.tpl"}
    {/block}
{/block}

{block name="frontend_index_content"}
    {block name="frontend_index_advanced_cart_content"}
        {include file="frontend/wishlist/index/content.tpl"}
    {/block}
{/block}
