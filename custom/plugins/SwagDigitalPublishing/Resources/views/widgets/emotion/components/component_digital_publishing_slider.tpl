{block name="widget_emotion_component_digital_publishing_slider"}
    <div class="emotion--digital-publishing-slider emotion--banner-slider"
         data-image-slider="true"
         data-thumbnails="false"
         data-lightbox="false"
         data-loopSlides="true"
         data-arrowControls="{if $Data.show_arrows}true{else}false{/if}"
         data-autoSlide="{if $Data.auto_slide}true{else}false{/if}"
         data-autoSlideInterval="{$Data.slide_interval}"
         data-animationSpeed="{$Data.animation_speed}"
         data-imageSelector=".image-slider--item">

        {block name="widget_emotion_component_digital_publishing_slider_container"}
            <div class="banner-slider--container image-slider--container">

                {block name="widget_emotion_component_digital_publishing_slider_slide"}
                    <div class="banner-slider--slide image-slider--slide">

                        {block name="widget_emotion_component_digital_publishing_slider_items"}
                            {foreach $Data.banners as $banner}
                                <div class="emotion--digital-publishing image-slider--item">
                                    {include file="widgets/swag_digital_publishing/index.tpl" banner=$banner}
                                </div>
                            {/foreach}
                        {/block}
                    </div>
                {/block}

                {block name="widget_emotion_component_digital_publishing_slider_dots"}
                    {if $Data.show_navigation}
                        <div class="image-slider--dots">
                            {foreach $Data.banners as $link}
                                <div class="dot--link">{$link@iteration}</div>
                            {/foreach}
                        </div>
                    {/if}
                {/block}
            </div>
        {/block}
    </div>
{/block}