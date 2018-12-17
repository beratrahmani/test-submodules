{extends file="parent:frontend/checkout/ajax_add_article.tpl"}

{block name="checkout_ajax_add_information"}
    {$smarty.block.parent}
    {block name="checkout_ajax_add_information_used_promotions"}
        {if $promotionsUsedTooOften}
            {include file="frontend/swag_promotion/checkout/used_too_often.tpl"}
        {/if}
        {if $promotionsDoNotMatch}
            {include file="frontend/swag_promotion/checkout/does_not_match.tpl"}
        {/if}
    {/block}
    {block name="checkout_ajax_add_information_promotion"}
        {if !empty($freeGoods)}
            {include file="frontend/swag_promotion/checkout/free_goods_hint.tpl"}
        {/if}
    {/block}
{/block}
