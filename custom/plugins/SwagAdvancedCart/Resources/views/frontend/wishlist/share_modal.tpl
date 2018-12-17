{namespace name="frontend/plugins/swag_advanced_cart/modal_share"}

{block name="frontend_wishlist_index_modal_share"}
    <div class="cart--wishlist-modal">
        {block name="frontend_checkout_cart_modal_share_inner"}
            <div class="wishlist-modal--inner">

                {* Error & Success Alert Box *}
                {block name="frontend_checkout_cart_modal_share_inner_alert"}
                    <div class="cart--share-alert">
                        {include file="frontend/_includes/messages.tpl" type="success" content="{s name="listShared" namespace="frontend/plugins/swag_advanced_cart/controller_messages"}{/s}"}
                    </div>
                {/block}

                {block name="frontend_checkout_cart_modal_share_inner_form"}
                    <form action="{url controller=wishlist action=share}" method="post" id="inner--cart-share">
                        {* E-Mail Message *}
                        {block name="frontend_checkout_cart_modal_share_form_from"}
                            <div class="share">
                                <strong>{s name='From'}{/s}: &nbsp;</strong> {$name} ({$eMail})
                            </div>
                        {/block}
                        {block name="frontend_checkout_cart_modal_share_form_to"}
                            <div class="cart--modal-share-container">
                                <label class="cart--label" for="name">{s name='To'}{/s}:</label>
                                <textarea name="to" class="cart--textarea" placeholder="{s name='ToPlaceholder'}{/s}"></textarea>
                            </div>
                        {/block}

                        {block name="frontend_checkout_cart_modal_share_form_message"}
                            <div class="cart--modal-share-container">
                                <label class="cart--label" for="name">{s name='Message'}{/s}:</label>
                                <textarea name="message" class="cart--textarea cart--mail-message">{s name='ShareMessage'}{/s}</textarea>
                            </div>
                        {/block}

                        {block name="frontend_checkout_cart_modal_share_form_hidden"}
                            <input type="hidden" id="cart--public-url" name="hash" value="{$hash}">
                        {/block}

                        {block name="frontend_checkout_cart_modal_share_form_button"}
                            <button class="cart--modal-share-btn btn is--primary" type="submit">{s name='Send'}{/s}</button>
                        {/block}
                    </form>
                {/block}
            </div>
        {/block}
    </div>
{/block}
