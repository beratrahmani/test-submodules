<div class="bundle--slider-content image--slider-content" data-swagBundleSlider="true">

    {block name='image--slider-navigation'}
        <a class="product-slider--arrow arrow--next is--horizontal bundle--arrow-next"></a>
        <a class="product-slider--arrow arrow--prev is--horizontal bundle--arrow-prev"></a>
    {/block}

    {block name='image_slider'}
        <div class="content--image-slider product-slider" data-bundleProductSlider="true">

            {block name='image_slider_container'}
                <div class="bundle--image-slider-container">

                    {foreach $bundle.articles as $product}
                        {block name='container_item'}
                            <div class="bundle--container-item" data-bundleImageProductId="{if $product.bundleArticleId}{$product.bundleArticleId}{else}0{/if}">

                                {* item seperator *}
                                {block name='item_separator'}
                                    {if !$product@first}
                                        <div class="item--separator is--bold"><i class="icon--plus3"></i></div>
                                    {/if}
                                {/block}

                                {* product image *}
                                {block name='item_image'}
                                    <div class="product--image">
                                        {block name='item_image_element'}
                                            {strip}
                                                <a class="bundle--product-slider-image" title="{$product.name|escape:'html'}" href="{url controller=detail sArticle=$product.articleId}">
                                                    {if isset($product.cover.src)}
                                                        <img class="image--bundle-img" srcset="{$product.cover.src[0]}" alt="{$product.name|escape:'html'}"/>
                                                    {else}
                                                        <img class="image--bundle-img" srcset="{link file='frontend/_public/src/img/no-picture.jpg'}" alt="{$product.name|escape:'html'}"/>
                                                    {/if}
                                                </a>
                                            {/strip}
                                        {/block}
                                    </div>
                                {/block}

                            </div>
                        {/block}
                    {/foreach}
                </div>
            {/block}
        </div>
    {/block}
</div>
