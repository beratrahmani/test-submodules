{block name="widgets_swag_emotion_advanced_buy_button"}
    {if $sArticle.sConfigurator && !$activeConfiguratorSelection}
        {block name="widgets_swag_emotion_advanced_buy_button_disabled_btn"}
            <button class="buybox--button block btn is--disabled is--icon-right is--large" disabled="disabled" aria-disabled="true" name="{s namespace="frontend/detail/buy" name="DetailBuyActionAddName"}{/s}"{if $buy_box_display} style="{$buy_box_display}"{/if}>
                {s namespace="frontend/listing/box_article" name="ListingBuyActionAdd"}{/s}<i class="icon--basket"></i> <i class="icon--arrow-right"></i>
            </button>
        {/block}
    {else}
        {block name="widgets_swag_emotion_advanced_buy_button_enabled_btn"}
            <button class="buybox--button block btn is--primary is--icon-right is--center is--large" name="{s namespace="frontend/detail/buy" name="DetailBuyActionAddName"}{/s}"{if $buy_box_display} style="{$buy_box_display}"{/if}>
                {s namespace="frontend/listing/box_article" name="ListingBuyActionAdd"}{/s}<i class="icon--basket"></i> <i class="icon--arrow-right"></i>
            </button>
        {/block}
    {/if}
{/block}
