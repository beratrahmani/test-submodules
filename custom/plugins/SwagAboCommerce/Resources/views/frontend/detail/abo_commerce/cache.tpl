{namespace name="frontend/plugins/abo_commerce"}

{block name='frontend_abo_commerce_detail_data_interval'}
    <span class="abo--commerce-delivery-interval-unit is--hidden">
		{block name="frontend_abo_commerce_detail_data_interval_value"}
            {if $aboCommerce.deliveryIntervalUnit == "weeks"}
                <span>
					{s name="AboCommerceIntervalSelectWeeks"}{/s}
				</span>
            {else}
                <span>
					{s name="AboCommerceIntervalSelectMonths"}{/s}
				</span>
            {/if}
        {/block}
	</span>
{/block}

{block name='frontend_abo_abo_commerce_detail_data_duration'}
    <span class="abo--commerce-duration-unit is--hidden">
		{if $aboCommerce.durationUnit == "weeks"}
            {s name="AboCommerceDurationSelectWeeks"}{/s}
        {else}
            {s name="AboCommerceDurationSelectMonths"}{/s}
        {/if}
	</span>
{/block}

{block name='frontend_abo_abo_commerce_detail_data_abo_data'}
    <div class="abo--commerce-data is--hidden">{$aboCommerce|json_encode}</div>
{/block}

{block name='frontend_abo_abo_commerce_detail_data_block_price_data'}
    <div class="abo--block-prices-data is--hidden">{$sArticle.sBlockPrices|json_encode}</div>
{/block}

{block name='frontend_abo_abo_commerce_detail_data_price_data'}
    <div class="abo--price-template-data is--hidden">{'0'|currency}&nbsp;{s name="Star" namespace="frontend/listing/box_article"}{/s}</div>
{/block}
