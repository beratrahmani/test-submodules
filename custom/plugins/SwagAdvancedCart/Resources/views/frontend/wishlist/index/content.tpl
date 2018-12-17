{namespace name="frontend/plugins/swag_advanced_cart/plugin"}
{block name="frontend_wishlist_index_container"}
    <div class="content account--content">
        {if $storedWishlist}
            {include file="frontend/_includes/messages.tpl" type="warning" content="{s name='WishlistProductWaitingWarning'}{/s}"}
        {/if}

        {if $sErrorFlag}
            {include file="frontend/_includes/messages.tpl" type="error" content="{s name='WishlistCreateError'}{/s}"}
        {/if}

        {block name="frontend_wishlist_index_container_headline"}
            <div class="account--welcome panel">
                <h1 class="panel--title">{s name='Title'}{/s}</h1>
                <p>
                    {s name='Teaser'}{/s}
                </p>
            </div>
        {/block}

        {block name="frontend_wishlist_index_container_body"}
            <div class="panel has--border">
                {block name="frontend_wishlist_index_container_body_title"}
                    <h1 class="panel--title wishlist-content--table-headline">{s name='CreateWishlist'}{/s}</h1>
                {/block}

                <div class="panel--body">
                    {block name="frontend_wishlist_index_container_body_table"}
                        <div class="wishlist-content--table-content">
                            {block name="frontend_wishlist_index_container_body_form"}
                                <form action="{url controller=wishlist action=createcart}" method="post">
                                    <input type="text" class="table-content--text-field" name="name" placeholder="{s name='WishlistPlaceholder'}{/s}">
                                    {block name="frontend_wishlist_index_container_body_form_button"}
                                        <button type="submit" class="table-content--create-button btn is--secondary">{s name='CreateWishlist'}{/s}</button>
                                    {/block}
                                </form>
                            {/block}
                        </div>
                    {/block}
                    <div class="cart--clear"></div>
                </div>
            </div>
        {/block}

        {block name="frontend_wishlist_index_container_info"}
            {block name="frontend_wishlist_index_container_info_text"}
                <h1>{s name='MyWishlists'}{/s}</h1>
                <p>
                    {s name='ControlOwnWishlists'}{/s}
                </p>
            {/block}
            <div class="wishlist-content--saved-lists">
                {foreach from=$savedBaskets item=wishList name=listIteration}
                    {block name="frontend_wishlist_index_container_item"}
                        {include file="frontend/wishlist/list.tpl" wishList=$wishList first=$smarty.foreach.listIteration.first}
                    {/block}
                {/foreach}
            </div>
        {/block}
    </div>
{/block}
