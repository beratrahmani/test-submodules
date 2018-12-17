{namespace name="frontend/plugins/swag_advanced_cart/article_detail"}

{block name="frontend_detail_index_modal_wishlist"}
    {block name="frontend_detail_index_modal_alert"}
        {if $customizable}
            <div class="alert is--warning is--rounded">
                <div class="alert--icon">
                    <i class="icon--element icon--warning"></i>
                </div>
                <div class="alert--content">
                    {s namespace="frontend/swag_advanced_cart/view/main" name="CustomizedWarningContent"}{/s}
                </div>
            </div>
        {/if}
    {/block}

    {block name="frontend_advanced_cart_alert_min_one"}
        <div class="add-article--wishlist-alert wishlist-alert--min-one">
            {include file="frontend/_includes/messages.tpl" type="error" content="{s name='AddListMinOneList'}{/s}"}
        </div>
    {/block}

    {block name="frontend_advanced_cart_alert_add_error"}
        <div class="add-article--wishlist-alert wishlist-alert--add-error">
            {include file="frontend/_includes/messages.tpl" type="error" content="{s name="AddListError"}{/s}"}
        </div>
    {/block}
    <div class="cart--wishlist-modal">
        {block name="frontend_detail_index_modal_inner"}
            <div class="wishlist-modal--inner">

                {block name="frontend_detail_index_modal_inner_form"}
                    <form action="{url controller=wishlist action=addToList}" method="post"
                          class="cart--form-add-article {if !$userId}cart--is-centered{/if}">

                        {* Info Text*}
                        {if $userId}
                            {block name="frontend_detail_index_modal_inner_form_loggedin"}
                                {block name="frontend_detail_index_modal_inner_form_loggedin_info"}
                                    <div class="inner--info-text cart--modal-container">
                                        {block name="frontend_detail_index_modal_inner_form_loggedin_text"}
                                            {s name="SaveTheArticle"}{/s}
                                            <strong>{$sArticle.articleName}</strong>
                                            {s name='inExistingWishlist'}{/s}
                                        {/block}
                                    </div>
                                {/block}
                                {block name="frontend_detail_index_modal_inner_form_loggedin_lists"}
                                    <div class="inner--current-wishlists cart--modal-container">
                                        <strong>{s name="ExistingList"}{/s}:</strong>

                                        {foreach from=$allCartsByUser item=cart}
                                            {block name="frontend_detail_index_modal_inner_form_loggedin_list"}
                                                <div class="current-wishlists--item">
                                                    <input class="cart-item--input" id="cart--list{$cart.id}" type="checkbox" name="lists[]"
                                                           value="{$cart.id}">
                                                    <label class="cart-item--label" for="cart--list{$cart.id}">{$cart.name}
                                                        ({$cart.cartItems|count} {s name='Article'}Artikel{/s})</label>
                                                </div>
                                            {/block}
                                            {foreachelse}
                                            {block name="frontend_detail_index_modal_inner_form_loggedin_new"}
                                                <p class="cart--new-list">{s name='CreateWishlist'}{/s}</p>
                                            {/block}
                                        {/foreach}
                                    </div>
                                {/block}
                                {block name="frontend_detail_index_modal_inner_form_loggedin_create"}
                                    <div class="inner--add-wishlist cart--modal-container">
                                        <label class="add-wishlist--label"
                                               for="name">{s name='NewWishlist'}{/s}:</label>
                                        <input type="text" name="newlist"
                                               placeholder="{s name='DefineName'}{/s}"
                                               class="add-wishlist--name">
                                        <input type="hidden" name="ordernumber" value="{$sArticle.ordernumber}">
                                    </div>
                                {/block}
                                {block name="frontend_detail_index_modal_inner_form_loggedin_quantity"}
                                    <input type="hidden" name="quantity" value="{$quantity}">
                                {/block}
                                {block name="frontend_detail_index_modal_inner_form_loggedin_button"}
                                    <a class="add-wishlist--button is--primary btn">{s name='Add'}{/s}</a>
                                {/block}
                            {/block}
                        {else}
                            {block name="frontend_detail_index_modal_inner_form_notloggedin"}
                                <p class="cart--login-text">{s name='WishlistTeaserText' namespace='frontend/plugins/swag_advanced_cart/checkout_notloggedin'}{/s}</p>
                                {block name="frontend_detail_index_modal_inner_form_notloggedin_button"}
                                    <a href="{url controller=wishlist}"
                                       class="is--primary btn small">{s name='RegisterOrLogin'}{/s}</a>
                                {/block}
                            {/block}
                        {/if}
                    </form>
                {/block}
            </div>
        {/block}
    </div>
{/block}
