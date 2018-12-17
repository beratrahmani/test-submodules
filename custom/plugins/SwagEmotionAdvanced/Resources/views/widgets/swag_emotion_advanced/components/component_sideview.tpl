{namespace name='widgets/emotion/components/component_sideview'}
<div class="emotion--side-view"
     data-coverImage="true"
     data-containerSelector=".side-view--banner"
     data-width="{$Data.banner_data.width}"
     data-height="{$Data.banner_data.height}"
     data-autoScroll="{if $Data.sideview_auto_start == 1}true{else}false{/if}">

    {block name="widget_emotion_component_sideview_banner"}
        <div class="side-view--banner {$Data.sideview_bannerposition}">

            {if $Data.banner_data.thumbnails}
                {$baseSource = $Data.banner_data.thumbnails[0].source}
                {$retinaBaseSource = $Data.banner_data.thumbnails[0].retinaSource}
                {$colSize = 100 / $emotion.grid.cols}
                {$itemSize = $itemCols * $colSize}

                {foreach $element.viewports as $viewport}
                    {$cols = ($viewport.endCol - $viewport.startCol) + 1}
                    {$elementSize = $cols * $cellWidth}
                    {$size = "{$elementSize}vw"}

                    {if $breakpoints[$viewport.alias]}

                        {if $viewport.alias === 'xl' && !$emotionFullscreen}
                            {$size = "calc({$elementSize / 100} * {$baseWidth}px)"}
                        {/if}

                        {$size = "(min-width: {$breakpoints[$viewport.alias]}) {$size}"}
                    {/if}

                    {$itemSize = "{$size}{if $itemSize}, {$itemSize}{/if}"}
                {/foreach}

                {foreach $Data.banner_data.thumbnails as $image}
                    {$srcSet = "{if $srcSet}{$srcSet}, {/if}{$image.source} {$image.maxWidth}w"}

                    {if $image.retinaSource}
                        {$srcSetRetina = "{if $srcSetRetina}{$srcSetRetina}, {/if}{$image.retinaSource} {$image.maxWidth}w"}
                    {/if}
                {/foreach}
            {else}
                {$baseSource = $Data.banner_data.source}
            {/if}

            <picture class="banner--image">
                <source sizes="{$itemSize}" srcset="{$srcSetRetina}" media="(min-resolution: 192dpi), (-webkit-min-device-pixel-ratio: 2)">
                <source sizes="{$itemSize}" srcset="{$srcSet}">

                {* Fallback *}
                <img src="{$baseSource}" srcset="{$retinaBaseSource} 2x" class="banner--image"{if $Data.banner_data.title} alt="{$Data.banner_data.title|escape}"{/if}/>
            </picture>
        </div>
    {/block}

    {block name="widget_emotion_component_sideview_view"}
        <div class="side-view--view view--{$Data.sideview_position} view--{$Data.sideview_size}">

            {block name="widget_emotion_component_sideview_trigger"}
                <div class="side-view--trigger">
                    <i class="trigger--icon"></i>
                    <span class="trigger--label">{s name="sideViewTriggerLabel"}{/s}</span>
                </div>
            {/block}

            {block name="widget_emotion_component_sideview_close_button"}
                {if $Data.sideview_size == 'fullsize'}
                    <div class="side-view--closer">
                        <i class="closer--icon"></i>
                        <span class="closer--label">{s name="sideViewCloseLabel"}{/s}</span>
                    </div>
                {/if}
            {/block}

            {block name="widget_emotion_component_sideview_slider"}

                {$orientation = 'horizontal'}

                {if $Data.sideview_position == 'right'}
                    {$orientation = 'vertical'}
                {/if}
                <div class="product-slider"
                     data-product-slider="true"
                     data-orientation="{$orientation}"
                     data-arrowControls="{if $Data.sideview_show_arrows == 1}true{else}false{/if}"
                     {if $Data.ajaxFeed}
                     data-mode="ajax"
                     data-ajaxCtrlUrl="{$Data.ajaxFeed}"
                     {/if}
                     {if $Data.sideview_size == 'fullsize'}
                     data-itemMinWidth="320"
                     data-itemMinHeight="340"
                     {/if}>

                    {block name="widget_emotion_component_sideview_slider_container"}
                        <div class="product-slider--container">

                            {foreach $Data.product_data as $item}
                                {block name="widget_emotion_component_sideview_slider_item"}
                                    <div class="product-slider--item">
                                        {include file="frontend/listing/box_article.tpl" sArticle=$item productBoxLayout="emotion"}
                                    </div>
                                {/block}
                            {/foreach}
                        </div>
                    {/block}
                </div>
            {/block}
        </div>
    {/block}
</div>
