{if empty({config name=replaceNote})}
    {block name="frontend_wishlist_sidebar"}
        <div class="cart--sidebar-note sidebar--navigation">
            {block name="frontend_wishlist_sidebar_link"}
                <a class="sidebar-note--link navigation--link" href="{url controller=wishlist}">
                    <i class="icon--text"></i>
                    {s name="WishlistsTitle" namespace='frontend/plugins/swag_advanced_cart/content_right'}{/s}
                    <span class="is--icon-right">
						<i class="icon--arrow-right"></i>
					</span>
                </a>
            {/block}
        </div>
    {/block}
{/if}
