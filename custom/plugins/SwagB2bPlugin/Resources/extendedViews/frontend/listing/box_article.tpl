{extends file="parent:frontend/listing/box_article.tpl"}

{block name="frontend_listing_box_article_includes"}
    {if $b2bProductBoxLayout == 'table'}
        <tr>
            <td class="col-ordernumber">
                <a href="{$sArticle.linkDetails}" class="product--ordernumber"
                   title="{$sArticle.articleName|escapeHtml}">
                    {$sArticle.ordernumber}
                </a>
            </td>
            <td class="col-products">
                <a href="{$sArticle.linkDetails}" class="product--title"
                   title="{$sArticle.articleName|escapeHtml}">
                    {$sArticle.articleName|truncate:50|escapeHtml}
                </a>
            </td>
            <td class="col-products-price">
                <div class="product--price-info is--align-right">
                    {* Product price - Unit price *}
                    {block name="frontend_listing_box_article_unit"}
                        {include file="frontend/listing/product-box/product-price-unit.tpl"}
                    {/block}

                    {* Product price - Default and discount price *}
                    {block name="frontend_listing_box_article_price"}
                        {include file="frontend/listing/product-box/product-price.tpl"}
                    {/block}
                </div>
            </td>
            <td class="col-article-quantity">
                <input type="hidden" name="products[{$iteration}][referenceNumber]" value="{$sArticle.ordernumber}" />
                <input type="number" min="1" name="products[{$iteration}][quantity]" tabindex="{$iteration}" class="b2b-table-quantity"/>
            </td>
        </tr>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}