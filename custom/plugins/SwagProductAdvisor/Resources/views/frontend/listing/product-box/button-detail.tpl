{extends file="parent:frontend/listing/product-box/button-detail.tpl"}

{* Hide the details button for the tophit to avoid duplicated buttons *}
{block name="frontend_listing_product_box_button_detail"}
	{if $productBoxLayout != 'advisor-tophit'}
		{$smarty.block.parent}
	{/if}
{/block}