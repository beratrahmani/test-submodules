{block name='products_detail_wrapper'}
    <div class="bundle--detail-container"
         data-bundleId="{$bundle.id}"
         data-swagBundleVariantConfiguration="true"
         data-saveProductConfigurationsUrl="{url module=widgets controller=Bundle action=configureProducts}">
        <div class="bundle--detail-error-container alert is--error is--rounded is--hidden">
            <div class="alert--icon">
                <div class="icon--element icon--warning"></div>
            </div>
            <div class="alert--content">
                {s name=errorMessage namespace=frontend/detail/bundle}{/s}
            </div>
        </div>

        {foreach $bundle.articles as $product}
            {if $product.articleName}
                {$productName = $product.articleName}
            {else}
                {$productName = $product.name}
            {/if}

            {if $product.additionalText}
                {$productName = $productName|cat:' '|cat:$product.additionalText}
            {/if}
            <div class="detail--wrapper{if $product@first} is--first{/if}{if $product@last} is--last{/if}"
                 data-bundleProductId="{if $product.bundleArticleId}{$product.bundleArticleId}{else}0{/if}"
                 data-bundleId="{$bundle.id}"
                 data-productPrice="{$product.price.total}">

                {* product checkbox  *}
                {block name='wrapper_cross_selling'}
                    {if $bundle.type === 2 && !$product@first}
                        <span class="bundle--product-checkbox">
                            <input type="checkbox"
                                   name="bundle-product-{$product.bundleArticleId}"
                                   checked="checked"
                                   data-bundleId="{$bundle.id}"
                                   data-bundleProductId="{$product.bundleArticleId}"
                                   data-bundleProductSelection="true"/>

                            <span class="checkbox--state">&nbsp;</span>
                        </span>
                    {/if}
                {/block}

                {* product details *}
                {block name='bundle_wrapper_article'}
                    <div class="bundle--wrapper-product{if $bundle.type === 2} selective--product{/if}{if $product.noProductInStock} not--buyable{/if} bundle--id-{$bundle.id}">

                        {* product image *}
                        {block name='bundle_article_image'}
                            <div class="bundle--product-list-image">
                                <img src="{if $product.cover.src.0}{$product.cover.src.0}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" alt="{$productName|escape}">
                            </div>
                        {/block}

                        {* product name *}
                        {block name='bundle_article_name'}
                            <div class="bundle--product-name is--bold">
                                <a href="{url controller=detail sArticle=$product.articleId}" title="{$productName|escape}">{$product.quantity}x {$productName|escape}</a>
                            </div>
                        {/block}

                        {* product supplier and price *}
                        {block name='bundle_article_price_supplier'}
                            <div class="bundle--product-price-supplier">
                                {if $product.supplier}
                                    <span class="bundle--product-supplier"> - {$product.supplier} -</span>
                                {/if}
                                &nbsp;
                                <span class="bundle--product-price is--bold" data-bundleProductId="{$product.bundleArticleId}" data-bundleProductPrice="{$product.price.total}" data-bundleId="{$bundle.id}">
                                    {$product.price.total|currency}
                                <span>
                                {s name="Star" namespace="frontend/listing/box_article"}{/s}
                            </div>
                        {/block}

                        {* product delivery time *}
                        {block name='bundle_article_delivery_time'}
                            {if  $bundle.displayDelivery == 1 OR $bundle.displayDelivery == 3}
                                <div class="bundle--product-delivery">
                                    {if $product.sReleaseDate && $product.sReleaseDate|date_format:"%Y%m%d" > $smarty.now|date_format:"%Y%m%d"}
                                        <span class="delivery--status-two">{s namespace="frontend/plugins/index/delivery_informations" name="DetailDataInfoShipping"}{/s} {$product.sReleaseDate|date:'date_long'}</span>
                                    {elseif $product.esd}
                                        <span class="delivery--status-one">{s namespace="frontend/plugins/index/delivery_informations" name="DetailDataInfoInstantDownload"}{/s}</span>
                                    {elseif $product.instock > 0}
                                        <span class="delivery--status-one">{s namespace="frontend/plugins/index/delivery_informations" name="DetailDataInfoInstock"}{/s}</span>
                                    {elseif $product.shippingtime}
                                        <span class="delivery--status-two">{s namespace="frontend/plugins/index/delivery_informations" name="DetailDataShippingtime"}{/s} {$product.shippingtime} {s namespace="frontend/plugins/index/delivery_informations" name="DetailDataShippingDays"}{/s}</span>
                                    {else}
                                        <span class="delivery--status-three">{s namespace="frontend/plugins/index/delivery_informations" name="DetailDataNotAvailable"}{/s}</span>
                                    {/if}
                                </div>
                            {/if}
                        {/block}

                        {* product configuration for variant products *}
                        {block name='bundle_article_configuration'}
                            {if $product.configuration|@count > 0}
                                <div class="bundle--product-configuration" data-bundleProductId="{if $product.bundleArticleId}{$product.bundleArticleId}{else}0{/if}">
                                    {block name='bundle_configuration_group'}
                                        {foreach $product.configuration as $group}
                                            {block name='bundle_group_selector'}
                                                <div class="configuration-selector">
                                                    {* configuration label *}
                                                    {block name='bundle_selector_label'}
                                                        <label for="group-{$group.id}" class="is--bold">
                                                            {* check if a translation exists *}
                                                            {if $group.groupname == ""}
                                                                {$group.name}:
                                                            {else}
                                                                {* If a translation exists, we can use it to display the group *}
                                                                {$group.groupname}:
                                                            {/if}
                                                        </label>
                                                    {/block}

                                                    {* configuration select *}
                                                    {block name='bundle_selector_select'}
                                                        <select id="group-{$group.id}" name="group-{$group.id}" data-defaultValue="{$group.selected}">
                                                            {block name='bundle_select_option'}
                                                                {foreach $group.options as $option}
                                                                    <option value="{$option.id}"{if $option.id == $group.selected} selected="selected"{/if}>
                                                                        {* check if a translation exists *}
                                                                        {if $option.optionname == ""}
                                                                            {$option.name}
                                                                        {else}
                                                                            {* If a translation exists, we can use it to display the option *}
                                                                            {$option.optionname}
                                                                        {/if}
                                                                    </option>
                                                                {/foreach}
                                                            {/block}
                                                        </select>
                                                    {/block}
                                                </div>
                                            {/block}
                                        {/foreach}
                                    {/block}

                                    <button class="bundle--product-configuration-add btn is--primary is--center" disabled="disabled">
                                        {s name="sConfigurationTake" namespace="frontend/detail/bundle"}{/s}
                                    </button>
                                </div>
                            {/if}
                        {/block}
                    </div>
                {/block}

                {* product error message  *}
                {block name='wrapper_error_message'}
                    {if $product.noProductInStock}
                        {include file="frontend/_includes/messages.tpl" type="error" content="{$productName|escape} {s namespace="frontend/detail/bundle" name="CheckoutBundleError"}{/s}"}
                    {/if}
                {/block}
            </div>
        {/foreach}
    </div>
{/block}
