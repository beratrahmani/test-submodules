{extends file="parent:frontend/checkout/ajax_cart_item.tpl"}

{* Abo article product badge *}
{block name='frontend_checkout_ajax_cart_articleimage_badge_premium'}
    {if $basketItem.abo_attributes.isAboArticle}
        <span class="cart--badge">
            <span class="badge--free">{s name="AboCommerceBadge" namespace="frontend/checkout/abo_commerce_cart_item"}{/s}</span>
        </span>
    {/if}
{/block}

{block name="frontend_checkout_ajax_cart_articleimage_badge_rebate"}
    {$smarty.block.parent}
    {block name="frontend_checkout_ajax_cart_abo_commerce_rebate"}
        {include file="frontend/checkout/ajax_cart_item/rebate.tpl"}
    {/block}
{/block}

{block name="frontend_checkout_ajax_cart_actions"}
    {if $basketItem.modus != 10}
        {$smarty.block.parent}
    {/if}
{/block}