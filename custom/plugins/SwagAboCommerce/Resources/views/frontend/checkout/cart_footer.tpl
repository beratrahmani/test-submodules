{extends file="parent:frontend/checkout/cart_footer.tpl"}

{block name='frontend_checkout_cart_footer_add_product_field'}
    {$smarty.block.parent}
    <input type="hidden" name="swAboCommerceAddArticleFromCart" value="1">
{/block}