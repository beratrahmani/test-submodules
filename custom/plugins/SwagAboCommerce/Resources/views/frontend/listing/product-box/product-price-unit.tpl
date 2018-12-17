{extends file="parent:frontend/listing/product-box/product-price-unit.tpl"}

{block name='frontend_listing_box_article_unit_label'}
    {if $sArticle.attributes.swag_abo_commerce_prices && $sArticle.attributes.swag_abo_commerce_prices->get('cheapest_abo_price') !== NULL}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_listing_box_article_unit_content'}
    {if $sArticle.attributes.swag_abo_commerce_prices && $sArticle.attributes.swag_abo_commerce_prices->get('cheapest_abo_price') !== NULL}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}


{block name='frontend_listing_box_article_unit_reference_content'}
    {if $sArticle.attributes.swag_abo_commerce_prices && $sArticle.attributes.swag_abo_commerce_prices->get('cheapest_abo_price') !== NULL}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}