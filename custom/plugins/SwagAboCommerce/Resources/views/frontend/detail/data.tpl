{extends file='parent:frontend/detail/data.tpl'}

{* These two blocks are never used simultaneously *}
{* used if the product has a normal price *}
{block name='frontend_detail_data_price_default'}
    {if !$aboCommerce}
        {$smarty.block.parent}
    {else}
        {block name='frontend_detail_abo_commerce_data_price_default'}
            {include file='frontend/detail/data/default.tpl'}
        {/block}
    {/if}
{/block}

{* only used if the product has graduated prices *}
{block name='frontend_detail_data_block_price_include'}
    {$smarty.block.parent}
    {if $aboCommerce}
        {block name='frontend_detail_abo_commerce_data_block_price_include'}
            {include file='frontend/detail/data/default.tpl'}
        {/block}
    {/if}
{/block}

{block name='frontend_detail_data_price_unit_reference_content'}
    (<span class="reference--price">{$sArticle.referenceprice|currency}</span> {s name="Star" namespace="frontend/listing/box_article"}{/s}
    / {$sArticle.referenceunit} {$sArticle.sUnit.description})
{/block}