{extends file="parent:widgets/index/shop_menu.tpl"}

{block name="frontend_index_actions_active_shop" prepend}
    {block name="frontend_index_advanced_cart_actions_active_shop"}
        {include file="widgets/swag_advanced_cart/shop_menu.tpl"}
    {/block}
{/block}
