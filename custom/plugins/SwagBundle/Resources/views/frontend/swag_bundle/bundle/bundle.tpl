{block name='frontend_detail_index_bundle_container'}
    {block name='bundle_panel'}
        <div class="bundle--panel panel has--border is--wide is--hidden"
             data-swagBundle="true"
             data-discountType="{$bundle.discountType}"
             data-currencyHelper="{0|currency}"
             data-bundleId="{$bundle.id}"
             data-isBundleAvailableUrl="{url module=widgets controller=Bundle action=isBundleAvailable}"
             data-mainProductId="{$bundle.mainProductId}">

            {* bundle headline *}
            {block name='panel_header'}
                {if $bundle.showName}
                    <span class="bundle--panel-header panel--title is--underline">{$bundle.name}</span>
                {else}
                    <span class="bundle--panel-header panel--title is--underline">
                        {s namespace="frontend/detail/bundle" name="DetailBundleHeaeder"}{/s}
                        {if $bundle.discount.display !== '0'}
                            <i class="header--icon icon--percent"></i>
                        {/if}
                    </span>
                {/if}
            {/block}

            {block name='panel_content'}
                <div class="panel--content panel--body block-group">
                    {block name="panel_content_description"}
                        {if $bundle.description}
                            <div class="bundle--description">
                                {* Long description *}
                                {block name="panel_content_description_long"}
                                    <div class="teaser--text-long">
                                        {$bundle.description}
                                    </div>
                                {/block}

                                {* Short description *}
                                {block name="panel_content_description_short"}
                                    <div class="teaser--text-short is--hidden">
                                        {$bundle.description|strip_tags|truncate:200}
                                        <a href="#" title="{"{s namespace="frontend/listing/listing" name="ListingActionsOpenOffCanvas"}{/s}"|escape}" class="text--offcanvas-link">
                                            {s namespace="frontend/listing/listing" name="ListingActionsOpenOffCanvas"}{/s} &raquo;
                                        </a>
                                    </div>
                                {/block}

                                {* Off Canvas Container *}
                                {block name="panel_content_description_off_canvas"}
                                    <div class="teaser--text-offcanvas is--hidden">
                                        {* Close Button *}
                                        <a href="#" title="{"{s namespace="frontend/listing/listing" name="ListingActionsCloseOffCanvas"}{/s}"|escape}" class="close--off-canvas">
                                            <i class="icon--arrow-left"></i> {s namespace="frontend/listing/listing" name="ListingActionsCloseOffCanvas"}{/s}
                                        </a>

                                        {* Off Canvas Content *}
                                        {block name="panel_content_description_off_canvas_text"}
                                            <div class="offcanvas--content bundle--teaser-text">
                                                <div class="content--title">
                                                    {if $bundle.showName}
                                                        {$bundle.name}
                                                    {else}
                                                        {s namespace="frontend/detail/bundle" name="DetailBundleHeaeder"}{/s}
                                                    {/if}
                                                </div>
                                                {$bundle.description}
                                            </div>
                                        {/block}
                                    </div>
                                {/block}
                            </div>
                        {/if}
                    {/block}

                    {block name="panel_content_header"}
                        <div class="content--bundle-header">
                            {block name="panel_content_header_slider"}
                                <div class="bundle-header--slider-container block">
                                    {* image slider *}
                                    {block name='content_image_slider'}
                                        {include file='frontend/swag_bundle/bundle/container/bundle_image_slider.tpl'}
                                    {/block}
                                </div>
                            {/block}

                            {block name="panel_content_header_price"}
                                <div class="bundle-header--price-container block"
                                     data-swagBundlePriceHandler="true"
                                     data-discountPercentage="{$bundle.discount.percentage}"
                                     data-bundleId="{$bundle.id}">
                                    {* bundle price *}
                                    {block name='bundle_content_price'}
                                        {include file='frontend/swag_bundle/bundle/container/bundle_price.tpl'}
                                    {/block}

                                    {* cart button *}
                                    {block name='bundle_content_cart_button'}
                                        <div class="content--cart-button">
                                            <button class="btn is--primary is--icon-right" name="{s namespace="frontend/detail/bundle/box_related" name="BundleActionAdd"}{/s}">
                                                {s namespace="frontend/detail/bundle/box_related" name="BundleActionAdd"}{/s}<i class="icon--arrow-right"></i>
                                            </button>
                                        </div>
                                    {/block}
                                </div>
                            {/block}
                        </div>
                    {/block}


                    {* bundle products *}
                    {block name='content_products'}
                        <div class="content--products-container block">
                            {block name='products_header'}
                                <h4 class="products--header" data-bundleId="{$bundle.id}">
                                    {if $bundle.type == 2}
                                        {s namespace='frontend/detail/bundle/box_related' name='bundleHeaderActionShowBundleConfigurable'}{/s}
                                    {else}
                                        {s namespace='frontend/detail/bundle/box_related' name='bundleHeaderActionShow'}{/s}
                                    {/if}
                                    <i class="icon--arrow-down"></i>
                                </h4>
                            {/block}

                            {block name='products_content'}
                                <div class="products--content panel-body block">
                                    {include file='frontend/swag_bundle/bundle/container/bundle_products.tpl'}
                                </div>
                            {/block}

                            {block name='products_footer'}
                                <div class="products--footer is--bold" data-bundleId="{$bundle.id}">
                                    {s namespace='frontend/detail/bundle/box_related' name='bundleFooterActionShow'}{/s}
                                    <i class="icon--arrow-up"></i>
                                </div>
                            {/block}
                        </div>
                    {/block}
                </div>
            {/block}
        </div>
    {/block}
{/block}
