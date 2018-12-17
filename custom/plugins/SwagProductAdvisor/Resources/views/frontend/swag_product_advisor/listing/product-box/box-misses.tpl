{extends file="parent:frontend/swag_product_advisor/listing/product-box/box-hits.tpl"}

{* The template for the misses-template *}
{block name="frontend_listing_box_advisor_misses_ct"}
	{$smarty.block.parent}
    <ul class="advisor--misses-list advisor--list">
        {foreach $advisorAttribute->getMisses() as $miss}
            {block name="frontend_advisor_listing_hits_ct_miss"}
                <li class="misses-list--single-miss list--single-property">
                    <i class="icon--cross advisor--icon"></i>
                    <div class="single-miss--text single-property--text">{$miss['label']}</div>
                </li>
            {/block}
        {/foreach}
    </ul>
{/block}