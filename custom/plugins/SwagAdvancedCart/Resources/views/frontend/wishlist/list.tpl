{namespace name="frontend/plugins/swag_advanced_cart/plugin"}

<div class="saved-lists--list-container" data-is-active="{if $first}true{else}false{/if}">
    {block name="frontend_wishlist_index_list_row"}
        <div class="list-container--row">

            {block name="frontend_wishlist_index_list_row_icon"}
                <div class="list-container--lock-icon-container">
                    <i class="list-container--lock-icon {if $wishList.published}icon--eye{else}icon--lock{/if}"
                       title="{if $wishList.published}{s name='ListIsPublic'}{/s}{else}{s name='ListIsPrivate'}{/s}{/if}"></i>
                </div>
            {/block}

            {block name="frontend_wishlist_index_list_row_text"}
                <div class="list-container--text">
                    {block name="frontend_wishlist_index_list_row_text_name"}
                        <span class="list-container--text-name">{$wishList.name|escapeHtml}</span>
                    {/block}

                    {block name="frontend_wishlist_index_list_row_text_count"}
                        <div class="list-container--text-count">({$wishList.items|count} {s name='Article'}{/s})</div>
                    {/block}

                    {block name="frontend_wishlist_index_list_row_text_state"}
                        <div class="list-container--text-state">
                            {if $wishList.published}
                                {s name='PublicList'}{/s}
                            {else}
                                {s name='PrivateList'}{/s}
                            {/if}
                        </div>
                    {/block}
                </div>
            {/block}

            {block name="frontend_wishlist_index_list_row_collapse_icon"}
                <div class="list-container--icon-container">
                    <i class="list-container--icon icon--arrow-{if $first}up{else}down{/if}"></i>
                </div>
            {/block}
        </div>
    {/block}

    {block name="frontend_wishlist_index_list_content"}
        <div class="list-container--content{if !$first} cart--display-none{/if}">
            {block name="frontend_wishlist_index_list_content_hidden"}
                <div class="list-container--name-hidden">
                    <input id="name--field{$wishList.basketID}" type="text" name="list-container--name-input" class="list-container--name-input"
                           value="{$wishList.name|escapeHtmlAttr}" data-list-id="{$wishList.basketID}">
                </div>
            {/block}
            {block name="frontend_wishlist_index_list_content_header"}
                <div class="list-container--header">
                    {block name="frontend_wishlist_index_list_content_header_publish"}
                        <div class="header--publish-check">
                            <input data-list-id="{$wishList.basketID}" class="list-container--publish-check" id="list-container--publish-check{$wishList.basketID}"
                                   type="checkbox" {if $wishList.published}checked="checked"{/if}/>
                            <label for="list-container--publish-check{$wishList.basketID}">
                                <span class="is--italic">{s name='PrivacyText'}{/s}</span>
                            </label>
                        </div>
                    {/block}
                    {block name="frontend_wishlist_index_list_content_header_share"}
                        <div class="header--sharing-container{if !$wishList.published} list-container--disabled{/if}">
                            <p>
                                <span class="is--strong">{s name='LinkToShare'}{/s}:</span>
                                {block name="frontend_wishlist_index_list_content_header_share_input"}
                                    <span class="list-container--share-link">{url controller=wishlist action=public id=$wishList.hash}</span>
                                {/block}
                            </p>

                            {block name="frontend_wishlist_index_list_content_header_share_container"}
                                <div class="cart--share-container">
                                    {block name="frontend_wishlist_index_share_container_facebook"}
                                        <div class="public-list--action-facebook public-list--action-link {if !$wishList.published}cart--disabled{/if}">
                                            <a data-width="600" data-height="350" class="select-item--facebook select-item--item" href="https://www.facebook.com/sharer/sharer.php?u={url controller=wishlist action=public id=$wishList.hash}">
                                                <i class="icon--facebook"></i>
                                            </a>
                                        </div>
                                    {/block}

                                    {block name="frontend_wishlist_index_share_container_twitter"}
                                        <div class="public-list--action-twitter public-list--action-link {if !$wishList.published}cart--disabled{/if}">
                                            <a data-width="600" data-height="450" class="select-item--twitter select-item--item" href="https://twitter.com/share?url={url controller=wishlist action=public id=$wishList.hash}">
                                                <i class="icon--twitter"></i>
                                            </a>
                                        </div>
                                    {/block}

                                    {block name="frontend_wishlist_index_share_container_gplus"}
                                        <div class="public-list--action-google public-list--action-link {if !$wishList.published}cart--disabled{/if}">
                                            <a data-width="500" data-height="500" class="select-item--google-plus select-item--item" href="https://plus.google.com/share?url={url controller=wishlist action=public id=$wishList.hash}">
                                                <i class="icon--googleplus"></i>
                                            </a>
                                        </div>
                                    {/block}

                                    {block name="frontend_wishlist_index_share_container_mail"}
                                        {if {config name=shareViaMail}}
                                            <div class="public-list--action-google public-list--action-link {if !$wishList.published}cart--disabled{/if}">
                                                <a class="select-item--mail select-item--item" data-hash="{$wishList.hash}" href="">
                                                    <i class="icon--mail"></i>
                                                </a>
                                            </div>
                                        {/if}
                                    {/block}
                                </div>
                            {/block}
                        </div>
                    {/block}
                </div>
            {/block}
            {block name="frontend_wishlist_index_list_main"}
                <div>
                    {block name="frontend_wishlist_index_list_articles_buttons"}
                        <div class="article-table--add-article">
                            {block name="frontend_advanced_cart_alert_customizing"}
                                <div class="add-article--wishlist-alert wishlist-alert--customizing">
                                    {include file="frontend/_includes/messages.tpl" type="error" content="{s name="CustomizedWarningContent" namespace="frontend/swag_advanced_cart/view/main"}{/s}"}
                                </div>
                            {/block}

                            {block name="frontend_advanced_cart_alert_readded"}
                                <div class="add-article--wishlist-alert wishlist-alert--readded">
                                    {include file="frontend/_includes/messages.tpl" type="success" content="{s namespace="frontend/plugins/swag_advanced_cart/checkout" name="ArticleReAdded"}{/s}"}
                                </div>
                            {/block}

                            {block name="frontend_advanced_cart_alert_not_found"}
                                <div class="add-article--wishlist-alert wishlist-alert--not-found">
                                    {include file="frontend/_includes/messages.tpl" type="error" content="&nbsp;"}
                                </div>
                            {/block}

                            {block name="frontend_wishlist_index_list_articles_buttons_autocomplete"}
                                <input class="add-article--text-field"
                                       placeholder="{s name='ArticleNameOrOrdernumber'}{/s}"
                                       type="text"/>
                            {/block}

                            {block name="frontend_wishlist_index_list_articles_buttons_add"}
                                <button class="add-article--button btn is--primary">{s name='Add'}{/s}</button>
                            {/block}

                            {block name="frontend_wishlist_index_list_articles_buttons_hidden"}
                                <input class="add-article--hidden" type="hidden" name="basketId"
                                       value="{$wishList.basketID}">
                            {/block}

                            <div class="article-table--add-cart{if $wishList.items|count < 1} cart--hidden{/if}">
                                {include file="frontend/wishlist/restore_button.tpl"}
                            </div>

                        </div>
                    {/block}
                    {if $wishList.items|count > 0}
                        {include file="frontend/wishlist/article_table.tpl"}
                    {else}
                        <div class="cart--hide-container cart--hidden">
                            {include file="frontend/wishlist/article_table.tpl"}
                        </div>
                    {/if}
                </div>
            {/block}


            {block name="frontend_wishlist_index_list_main_buttons"}
                <div class="list-container--manage-container">
                    <div class="list-container--manage-buttons">
                        {include file="frontend/wishlist/restore_button.tpl"}

                        {block name="frontend_wishlist_index_manage_buttons"}
                            <div class="cart--manage-container">
                                {block name="frontend_wishlist_index_buttons_rename"}
                                    <label for="name--field{$wishList.basketID}" class="manage-container--rename btn" title="{s name='Rename'}{/s}">
                                        {block name="frontend_wishlist_index_buttons_rename_icon"}
                                            <i class="icon--pencil cart--rename-icon"></i>
                                        {/block}

                                        {block name="frontend_wishlist_index_buttons_rename_text"}
                                            <span class="rename--text">
												{s name='Rename'}{/s}
											</span>
                                        {/block}
                                    </label>
                                {/block}

                                {block name="frontend_wishlist_index_buttons_delete"}
                                    <button data-name="{$wishList.name|escapeHtmlAttr}" data-url="{url action='remove' id=$wishList.basketID}" class="manage-container--delete btn" title="{s name='Delete'}{/s}">
                                        {block name="frontend_wishlist_index_buttons_delete_icon"}
                                            <i class="icon--cross"></i>
                                        {/block}

                                        {block name="frontend_wishlist_index_buttons_delete_text"}
                                            <span class="delete--text">
												{s name='Delete'}{/s}
											</span>
                                        {/block}
                                    </button>
                                {/block}
                            </div>
                        {/block}
                    </div>
                </div>
            {/block}
        </div>
    {/block}
</div>
