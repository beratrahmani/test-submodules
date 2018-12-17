{extends file="parent:frontend/detail/content/buy_container.tpl"}

{* Product - Base information *}
{block name="frontend_detail_index_buy_container_base_info"}
    {$smarty.block.parent}
    {if $b2bSuite}
        {include file="frontend/detail/content/buy_container_inner.tpl"}
    {/if}
{/block}

{block name='frontend_detail_data_ordernumber_content'}
    {if $sArticle.attributes.b2b_ordernumber && $sArticle.attributes.b2b_ordernumber->get('custom_ordernumber')}
        <meta itemprop="productID" content="{$sArticle.articleDetailsID}"/>
        <span class="entry--content" itemprop="sku">
            {$sArticle.attributes.b2b_ordernumber->get('custom_ordernumber')}
        </span>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}