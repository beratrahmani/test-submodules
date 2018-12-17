{extends file="parent:frontend/listing/index.tpl"}

{block name="frontend_index_header_title"}
    {include file="frontend/abo_commerce/index/title.tpl"}
{/block}

{block name="frontend_index_left_menu"}
    {$smarty.block.parent}
    {include file="frontend/abo_commerce/left.tpl"}
{/block}

{block name="frontend_listing_index_topseller"}{/block}

{block name="frontend_listing_index_layout_variables"}
    {$smarty.block.parent}
    {include file="frontend/abo_commerce/index/variables.tpl" scope="parent"}
{/block}

{block name="frontend_listing_index_text"}
    {include file="frontend/abo_commerce/text.tpl"}
{/block}
