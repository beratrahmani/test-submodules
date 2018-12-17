{namespace name='frontend/plugins/swag_advanced_cart/public_list'}
{block name="frontend_wishlist_public"}
    {if $wishlist}
        {block name="frontend_wishlist_public_header"}
            <div class="public-list--header">
                {block name="frontend_wishlist_public_header_info"}
                    <div class="public-list--info">
                        {block name="frontend_wishlist_public_header_info_name"}
                            <h2>{$wishlist.name}</h2>
                        {/block}
                        {block name="frontend_wishlist_public_header_info_date"}
                            <p>
                                {s name='By'}{/s}
                                <strong>{$wishlist.user_firstname} {$wishlist.user_lastname}</strong>
                                {if $wishlist.modified|date:'DATE_MEDIUM'}
                                    - {s name='LastTimeEdited'}{/s}
                                    {if $wishlist.modified|date:'DATE_MEDIUM' == $smarty.now|date:'DATE_MEDIUM'}
                                        {s name='Today'}{/s},
                                    {elseif $wishlist.modified|date:'DATE_MEDIUM' == {"yesterday"|strtotime|date:'DATE_MEDIUM'}}
                                        {s name='Yesterday'}{/s},
                                    {else}
                                        {s name='OnDate'}{/s} {$wishlist.modified|date:'DATE_MEDIUM'},
                                    {/if}
                                    {s name='AtTime'}{/s} {$wishlist.modified|date:'TIME_SHORT'}
                                {/if}
                            </p>
                        {/block}
                    </div>
                {/block}

                {block name="frontend_wishlist_public_header_actions"}
                    <div class="public-list--action">
                        {block name="frontend_wishlist_public_header_actions_like"}
                            {if {config name=facebookLikes}}
                                <div class="public-list--action-link">
                                    <div class="public-list--action-like fb-like"
                                         data-href="{url controller=wishlist action=public id=$wishlist.hash}"
                                         data-layout="button_count" data-action="like" data-show-faces="false"
                                         data-share="false">&nbsp;</div>
                                </div>
                            {/if}
                        {/block}

                        {block name="frontend_wishlist_public_header_actions_facebook"}
                            <div class="public-list--action-facebook public-list--action-link">
                                <a data-width="600" data-height="350" class="select-item--facebook select-item--item" href="https://www.facebook.com/sharer/sharer.php?u={url controller=wishlist action=public id=$wishlist.hash}">
                                    <i class="icon--facebook"></i>
                                </a>
                            </div>
                        {/block}

                        {block name="frontend_wishlist_public_header_actions_twitter"}
                            <div class="public-list--action-twitter public-list--action-link">
                                <a data-width="600" data-height="450" class="select-item--twitter select-item--item" href="https://twitter.com/share?url={url controller=wishlist action=public id=$wishlist.hash}">
                                    <i class="icon--twitter"></i>
                                </a>
                            </div>
                        {/block}

                        {block name="frontend_wishlist_public_header_actions_google"}
                            <div class="public-list--action-google public-list--action-link">
                                <a data-width="500" data-height="500" class="select-item--google-plus select-item--item" href="https://plus.google.com/share?url={url controller=wishlist action=public id=$wishlist.hash}">
                                    <i class="icon--googleplus"></i>
                                </a>
                            </div>
                        {/block}

                        {block name="frontend_wishlist_public_header_actions_add"}
                            <div class="public-list--action-add">
                                <form action="{url controller=wishlist action=restore id={$wishlist.basketID}}">
                                    <button type="submit" class="public-list--action-btn btn is--primary">
                                        {s name='InsertWishlistIntoCart'}{/s}
                                    </button>
                                </form>
                            </div>
                        {/block}
                    </div>
                {/block}
            </div>
        {/block}

        {block name="frontend_wishlist_public_content"}
            <div class="public-list--content">
                <div class="list-container--article-table panel has--border">
                    <div class="article-table--table panel--table wishlist-has--padding" data-compare-ajax="true">
                        {block name="frontend_wishlist_public_content_table_header"}
                            <div class="article-table--header panel--tr">
                                {block name="frontend_wishlist_public_content_table_header_article"}
                                    <div class="panel--th column--article cart--public-article-column">
                                        {s name='Article' namespace="frontend/plugins/swag_advanced_cart/plugin"}{/s}
                                    </div>
                                {/block}

                                {block name="frontend_wishlist_public_content_table_header_price"}
                                    <div class="panel--th column--price">
                                        {s name='Price' namespace="frontend/plugins/swag_advanced_cart/plugin"}{/s}
                                    </div>
                                {/block}
                            </div>
                        {/block}

                        {foreach from=$wishlist.items item=item name=itemIteration}
                            {include file="frontend/wishlist/item_form.tpl" item=$item sBasketItem=$item.article hideDelete=true}
                        {/foreach}
                    </div>
                </div>
            </div>
        {/block}

        {block name="frontend_wishlist_public_content_comments"}
            {if {config name=facebookComments}}
                <div class="public-list--facebook-comments">
                    {block name="frontend_wishlist_public_content_comments_count"}
                        <div class="facebook-comments--table-head">
                            <span>
                                <div class="fb-like" data-href="{url controller=wishlist action=public id=$wishlist.hash}" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
                            </span>
                        </div>
                    {/block}

                    {block name="frontend_wishlist_public_content_comments_main"}
                        <div class="facebook-comments--table-row">
                            <div class="fb-comments" data-href="{url controller=wishlist action=public id=$wishlist.hash}"
                                 data-numposts="5" data-colorscheme="light" data-width="996" data-mobile="false"></div>
                        </div>
                    {/block}
                </div>
            {/if}
        {/block}
    {else}
        {block name="frontend_wishlist_public_notfound"}
            <div id="public-list--not-found">
                {* Headline *}
                {block name="frontend_wishlist_public_notfound_headline"}
                    <h2>{s namespace="frontend/plugins/swag_advanced_cart/public_list_notfound" name='Error404'}{/s}</h2>
                {/block}

                {block name="frontend_wishlist_public_notfound_text"}
                    <p>{s namespace="frontend/plugins/swag_advanced_cart/public_list_notfound" name='DoesntExistsOrDeleted'}{/s}</p>
                {/block}
            </div>
        {/block}
    {/if}
{/block}
