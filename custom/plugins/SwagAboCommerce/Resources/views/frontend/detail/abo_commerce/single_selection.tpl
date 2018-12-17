{* single price and radio button *}
{block name='abo_commerce_single_selection'}
    <div class="abo--selection">
        {block name='abo_commerce_single_selection_price'}
            <strong class="abo--single-delivery-price price--content content--default">

                {block name='abo_commerce_single_selection_price_input'}
                    <input class="abo--single-delivery-input selection" name="aboSelection" value="single" type="radio" checked="checked"/>
                {/block}

                {block name='abo_commerce_single_selection_price_meta'}
                    <meta itemprop="price" content="{$sArticle.price|replace:',':'.'}">
                    <span>{if $sArticle.priceStartingFrom && !$sArticle.liveshoppingData}{s name='ListingBoxArticleStartsAt' namespace="frontend/listing/box_article"}{/s} {/if}{$sArticle.price|currency}{s name="Star" namespace="frontend/listing/box_article"}{/s}</span>
                {/block}

            </strong>
        {/block}
    </div>
{/block}

{* single label text *}
{block name='abo_commerce_single_label'}
    <div class="abo--single-delivery-label-container">
        <label class="abo--single-delivery-label is--bold" for="single-delivery">{s name="AboCommerceDetailSingleDelivery" namespace="frontend/plugins/abo_commerce"}{/s}</label>
    </div>
{/block}
