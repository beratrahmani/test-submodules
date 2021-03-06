{extends file='parent:frontend/listing/product-box/button-buy.tpl'}
{block name="frontend_listing_product_box_button_buy_form"}
    <form name="sAddToBasket"
          method="post"
          action="{$url}"
          class="buybox--form"
          data-add-article="true"
          data-eventName="click" {* Rewrite "submit" to "click" so that the form is not being submitted as expected. *}
          {if $theme.offcanvasCart}
          data-showModal="false"
          data-addArticleUrl="{url controller=checkout action=ajaxAddArticleCart}"
          {/if}>

        {block name="frontend_listing_product_box_button_buy_order_number"}
            <input type="hidden" name="sAdd" value="{$sArticle.ordernumber}"/>
        {/block}

        {block name="frontend_listing_product_box_button_buy_button"}
            <button class="buybox--button block btn is--primary is--icon-right is--center is--large">
                {block name="frontend_listing_product_box_button_buy_button_text"}
                    {s namespace="frontend/listing/box_article" name="ListingBuyActionAdd"}{/s}<i class="icon--basket"></i> <i class="icon--arrow-right"></i>
                {/block}
            </button>
        {/block}
    </form>
{/block}
