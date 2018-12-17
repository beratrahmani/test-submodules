{namespace name="frontend/plugins/swag_advanced_cart/modal_confirm"}
{block name="frontend_wishlist_index_modal_confirm"}
    <div class="cart--wishlist-confirm-modal">
        {block name="frontend_wishlist_index_modal_confirm_inner"}
            <div class="wishlist-modal--inner">

                {block name="frontend_wishlist_index_modal_confirm_inner_text"}
                    {s name='ConfirmDialog'}{/s}
                    <strong class="cart--modal-list-name">{$wishListName}</strong>
                    {s name='ConfirmDialogEnd'}{/s}
                {/block}

                {block name="frontend_wishlist_index_modal_confirm_inner_button"}
                    <div class="cart--modal-btn-container">
                        <button href="{$deleteUrl}" class="modal-btn-container--btn btn is--primary">{s name='Delete'}{/s}</button>
                    </div>
                {/block}
            </div>
        {/block}
    </div>
{/block}
