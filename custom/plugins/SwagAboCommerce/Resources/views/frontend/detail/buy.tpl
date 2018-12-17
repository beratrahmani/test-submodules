{extends file='parent:frontend/detail/buy.tpl'}
{namespace name="frontend/detail/abo_commerce_detail"}

{* Modify the quantity select box *}
{block name='frontend_detail_buy_quantity'}
    {if $aboCommerce}
        {block name='frontend_detail_abo_commerce_buy_quantity'}
            {include file="frontend/detail/buy/quantity.tpl"}
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_detail_buy_configurator_inputs"}
    {$smarty.block.parent}

    {block name="frontend_detail_abo_commerce_buy_variant"}
        {include file="frontend/detail/buy/variant.tpl"}
    {/block}
{/block}
