{extends file="parent:frontend/listing/product-box/product-badges.tpl"}

{block name="frontend_listing_box_article_hint"}
    {$attribute = $sArticle.attributes.swag_abo_commerce}

    {if $sArticle.aboCommerce || ($attribute && $attribute->get('has_abo'))}
        {block name="frontend_listing_abo_commerce_box_article_hint"}
            {include file="frontend/listing/product-box/product-badges/hint.tpl"}
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_listing_box_article_new'}
    {$attribute = $sArticle.attributes.swag_abo_commerce}

    {if !$sArticle.aboCommerce || ($attribute && !$attribute->get('has_abo'))}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_listing_box_article_actions_buy_now"}
    {$attribute = $sArticle.attributes.swag_abo_commerce}

    {if !$sArticle.aboCommerceExclusive || ($attribute && !$attribute->get('exclusive'))}
        {$smarty.block.parent}
    {/if}
{/block}
