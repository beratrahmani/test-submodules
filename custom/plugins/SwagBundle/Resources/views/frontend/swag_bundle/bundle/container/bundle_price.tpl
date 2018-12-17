<div class="price--container block-group">
    {block name='container_price_description'}
        <div class="container--price-description block">
            {* bundle price *}
            {block name='container_price_value'}
                <div class="container--price-value">
                    {* bundle price *}
                    {block name='price_value_bundle_price'}
                        <span class="price--value-bundle-price is--bold" data-bundleId="{$bundle.id}">
                            {$bundle.price.display|currency}
                        </span>
                        <span class="price--value-bundle-star">
                            {s name="Star" namespace="frontend/listing/box_article"}{/s}
                        </span>
                    {/block}

                    {* regular price *}
                    {block name='price_value_regular_price'}
                        <span class="price--value-regular-price is--line-through" data-bundleId="{$bundle.id}">
                            {if $bundle.discount.display !== '0'}
                                {s namespace="frontend/detail/bundle" name="DetailBundleInstead"}{/s}
                                <span class="regular-price-total">{$bundle.totalPrice|currency}</span>
                                {s name="Star" namespace="frontend/listing/box_article"}{/s}
                            {/if}
                        </span>
                    {/block}
                </div>
            {/block}

            {* discount description *}
            {block name='container_price_text'}
                <div class="container--price-text">
                    <strong class="price--text">{s namespace="frontend/detail/bundle" name="DetailBundle"}{/s}</strong>
                </div>
            {/block}
        </div>
    {/block}

    {* delivery time *}
    {block name='container_delivery_time'}
        {if $bundle.displayDelivery == 2 || $bundle.displayDelivery == 3}
            <div class="delivery-time--container block">
                {if $longestShippingTimeProduct.sReleaseDate && $longestShippingTimeProduct.sReleaseDate|date_format:"%Y%m%d" > $smarty.now|date_format:"%Y%m%d"}
                    <span class="delivery--status-two">{s namespace="frontend/plugins/index/delivery_informations" name="DetailDataInfoShipping"}{/s} {$longestShippingTimeProduct.sReleaseDate|date:'date_long'}</span>
                {elseif $longestShippingTimeProduct.esd}
                    <span class="delivery--status-one">{s namespace="frontend/plugins/index/delivery_informations" name="DetailDataInfoInstantDownload"}{/s}</span>
                {elseif $longestShippingTimeProduct.instock > 0}
                    <span class="delivery--status-one">{s namespace="frontend/plugins/index/delivery_informations" name="DetailDataInfoInstock"}{/s}</span>
                {elseif $longestShippingTimeProduct.shippingtime}
                    <span class="delivery--status-two">{s namespace="frontend/plugins/index/delivery_informations" name="DetailDataShippingtime"}{/s} {$longestShippingTimeProduct.shippingtime} {s namespace="frontend/plugins/index/delivery_informations" name="DetailDataShippingDays"}{/s}</span>
                {else}
                    <span class="delivery--status-three">{s namespace="frontend/plugins/index/delivery_informations" name="DetailDataNotAvailable"}{config name=notavailable}{/s}</span>
                {/if}
            </div>
        {/if}
    {/block}
</div>
