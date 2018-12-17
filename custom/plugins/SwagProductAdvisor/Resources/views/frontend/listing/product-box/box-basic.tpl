{extends file="parent:frontend/listing/product-box/box-basic.tpl"}

{block name="frontend_listing_box_article_price_info"}
    {$smarty.block.parent}
    {block name="frontend_listing_box_article_price_info_swag_advisor"}
        {include file="frontend/swag_product_advisor/listing/action_button.tpl"}
    {/block}
{/block}

{block name="frontend_listing_box_article_image_picture_element"}
	{if $productBoxLayout != 'advisor-tophit'}
        {$smarty.block.parent}
    {else}
        {block name="frontend_listing_box_article_image_picture_element_swag_advisor"}
            {include file="frontend/swag_product_advisor/listing/tophit_image.tpl"}
        {/block}
    {/if}
{/block}