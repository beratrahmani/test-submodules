{namespace name="frontend/plugins/swag_advanced_cart/manage"}
{if count($wishlists) || !$isEmpty}
    {block name="frontend_wishlist_cart_header"}
        {block name="frontend_wishlist_cart_header_success"}
            <div class="cart--header-alert">
                {include file="frontend/_includes/messages.tpl" type="success" content="{s name='SaveCartSuccess'}{/s}"}
            </div>
        {/block}
        {block name="frontend_wishlist_cart_header_error"}
            <div class="cart--header-error" data-noName="{s name="SaveErrorEmptyName"}{/s}" data-nameExists="{s name="SaveErrorNameExists"}{/s}">
                {include file="frontend/_includes/messages.tpl" type="error" content="{s name='SaveCartError'}{/s}"}
            </div>
        {/block}
        {block name="frontend_wishlist_cart_header_swag_bundle_info"}
            <div class="cart--header-info-bundle">
                {include file="frontend/_includes/messages.tpl" type="info" content="{s name='SaveCartBundleMessage'}{/s}"}
            </div>
        {/block}
        <div class="cart--option-containers{if $isEmpty} cart--empty-basket{/if}{if !count($wishlists) OR $isEmpty} cart--half-option{/if}">
            {block name="frontend_wishlist_cart_header_icons"}
                <div class="cart--option-container option-container--icon-container">
                    {block name="frontend_wishlist_cart_header_icons_container"}
                        <div class="icon-container--container">
                            <i class="icon-container--icon icon--text"></i>
                            <i class="icon-container--icon icon--arrow-left3"></i>
                            <i class="icon-container--icon icon--arrow-right3"></i>
                            <i class="icon-container--icon icon--basket"></i>
                        </div>
                    {/block}
                </div>
            {/block}
            {block name="frontend_wishlist_cart_header_load"}
                {if count($wishlists)}
                    <div class="cart--option-container option-container--load-wishlist">
                        {block name="frontend_wishlist_cart_header_load_headline"}
                            <h4 class="option-container--headline">{s name="LoadList"}{/s}</h4>
                        {/block}

                        {block name="frontend_wishlist_cart_header_load_text"}
                            <span>
								{s name="LoadText"}{/s}
							</span>
                        {/block}

                        {block name="frontend_wishlist_cart_header_load_select"}
                            <select class="load-wishlist--select">
                                {block name="frontend_wishlist_cart_header_load_select_default"}
                                    <option class="load-wishlist--default-option">{s name="SelectEmptyText"}{/s}</option>
                                {/block}

                                {foreach $wishlists as $wishList}
                                    {block name="frontend_wishlist_cart_header_load_select_option"}
                                        <option data-wishlist-link="{url controller=wishlist action=restore id=$wishList.id}" class="load-wishlist--option">{$wishList.name}</option>
                                    {/block}
                                {/foreach}
                            </select>
                        {/block}

                    </div>
                {/if}
            {/block}

            {block name="frontend_wishlist_cart_header_save"}
                {if !$isEmpty}
                    {if $sUserLoggedIn}
                        <div class="cart--option-container option-container--save-wishlist">
                            {block name="frontend_wishlist_cart_header_save_headline"}
                                <h4 class="option-container--headline">{s name="SaveList"}{/s}</h4>
                            {/block}

                            {block name="frontend_wishlist_cart_header_save_text"}
                                <span>
									{s name="SaveText"}{/s}
								</span>
                            {/block}

                            {block name="frontend_wishlist_cart_header_save_input_container"}
                                <div class="save-wishlist--button-container">
                                    {block name="frontend_wishlist_cart_header_save_input_text"}
                                        <input name="name" class="save-wishlist--input" type="text" placeholder="{s name="SavePlaceHolder"}{/s}"/>
                                    {/block}

                                    {block name="frontend_wishlist_cart_header_save_input_button"}
                                        <button type="submit" class="save-wishlist--button add-product--button btn is--primary is--center block">
                                            <i class="icon--arrow-right"></i>
                                        </button>
                                    {/block}
                                </div>
                            {/block}
                        </div>
                    {else}
                        {block name="frontend_wishlist_cart_header_login"}
                            <div class="cart--option-container option-container--login-container">
                                <div class="login-container--wrapper">
                                    {block name="frontend_wishlist_cart_header_login_text"}
                                        <span class="login-container--text">
											{s name='WishlistTeaserText' namespace='frontend/plugins/swag_advanced_cart/checkout_notloggedin'}{/s}
										</span>
                                    {/block}

                                    {block name="frontend_wishlist_cart_header_login_button"}
                                        <a href="{url controller=wishlist action=loginCart}" class="is--primary btn small login-container--button">{s namespace="frontend/plugins/swag_advanced_cart/article_detail" name='RegisterOrLogin'}{/s}</a>
                                    {/block}
                                </div>
                            </div>
                        {/block}
                    {/if}
                {/if}
            {/block}
        </div>
    {/block}
{/if}
