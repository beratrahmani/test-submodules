{namespace name="frontend/listing/box_article"}

{block name='abo_commerce_abo_selection_price'}
    <div class="abo--delivery-price price--content content--default">
        <strong>
            {block name="abo_commerce_abo_selection_price_radio"}
                {if !$aboCommerce.isExclusive}
                    <input class="abo--delivery-input selection" name="aboSelection" value="abo" type="radio"/>
                {/if}
            {/block}
            {block name="abo_commerce_abo_selection_price_price"}
                <span class="delivery-price--price">{if !$sArticle.liveshoppingData}{s name='ListingBoxArticleStartsAt'}{/s} {/if}{$aboPrice.discountPrice|currency}{s name="Star"}{/s}</span>
            {/block}
        </strong>
    </div>
{/block}

{* discount *}
{block name='abo_commerce_abo_selection_discount'}
    {if $aboCommerce.hasDiscount}
        <div class="abo--pseudo-price{if $aboPrice.discountPercentage == 0} is--hidden{/if}">
            {block name="abo_commerce_abo_selection_discount_reduced"}
                <span class="is--line-through">
					{s name="reducedPrice"}{/s} <span class="original--price">{$sArticle.price|currency} {s name="Star"}{/s}</span>
				</span>
            {/block}
            {block name="abo_commerce_abo_selection_discount_percentage"}
                <div class="abo--percentage-container">
                    (<span class="percent">{$aboPrice.discountPercentage|number_format:2|replace:'.':','}</span>% {s namespace="frontend/detail/abo_commerce_detail" name="AboCommerceSaved"}{/s})
                </div>
            {/block}
        </div>
    {/if}
{/block}

{block name="abo_commerce_abo_selection_percent"}
    <div class="abo--percent-icon">%</div>
{/block}
