{block name="frontend_advisor_listing_wizard_actions"}
    {if $advisor['mode'] === 'wizard_mode'}
        <div class="advisor--listing-wizard-actions">
            {block name="frontend_advisor_listing_wizard_actions_last_question"}
                <div class="listing-wizard-actions--back-btn">
                    <a href="{$advisor['lastQuestionUrl']}" class="btn is--icon-left is--center">
                        {s name="LastQuestionBtnText" namespace="frontend/advisor/main"}Return to last question{/s}
                        <i class="icon--arrow-left"></i>
                    </a>
                </div>
            {/block}

            {block name="frontend_advisor_listing_wizard_actions_reset"}
                <div class="listing-wizard-actions--reset-btn">
                    <a class="advisor--reset-advisor-btn btn is--center is--icon-left" title="{s name="ResetAdvisorBtnText" namespace="frontend/advisor/main"}Reset advisor{/s}" href="{$advisorResetUrl}">
                        {s name="ResetAdvisorBtnText" namespace="frontend/advisor/main"}Reset advisor{/s}
                        <i class="icon--arrow-left"></i>
                    </a>
                </div>
            {/block}
        </div>
    {/if}
{/block}

{block name="frontend_advisor_listing_container_outer"}
    <div id="advisor-listing--container" class="advisor--listing"
         data-ajax-wishlist="true"
         data-compare-ajax="true"
         data-advisor-result="true">
        {$topHit = $advisor['topHit']}
        {if $topHit}
            {* Tophit container *}
            {block name="frontend_advisor_listing_tophit"}
                <div class="advisor--tophit-ct">
                    {if $advisor['topHitTitle']|trim}
                        {block name="frontend_advisor_listing_tophit_title"}
                            <div class="advisor--listing-title">
                                {$advisor['topHitTitle']|truncate:80}
                            </div>
                        {/block}
                    {/if}
                    {* Display the tophit equal to the chosen listing-layout *}
                    {if $advisor['listingLayout'] == 'show_matches'}
                        {block name="frontend_advisor_listing_tophit_hits"}
                            {include file="frontend/swag_product_advisor/listing/product-box/box-hits.tpl" product=$topHit sArticle=$topHit productBoxLayout='advisor-tophit'}
                        {/block}
                    {elseif $advisor['listingLayout'] == 'show_matches_and_misses'}
                        {block name="frontend_advisor_listing_tophit_hits_misses"}
                            {include file="frontend/swag_product_advisor/listing/product-box/box-misses.tpl" product=$topHit sArticle=$topHit productBoxLayout='advisor-tophit'}
                        {/block}
                    {else}
                        {block name="frontend_advisor_listing_tophit_basic"}
                            {include file="frontend/listing/product-box/box-basic.tpl" sArticle=$topHit productBoxLayout='advisor-tophit'}
                        {/block}
                    {/if}
                </div>
            {/block}
        {/if}

        {block name="frontend_advisor_listing_container"}
            {if $advisor['result']}
                <div class="advisor--listing-ct listing--wrapper">
                    {block name="frontend_advisor_listing_title"}
                        <div class="advisor--listing-title advisor--main-title">
                            {$othersTitle = $advisor['othersTitle']}
                            {$replaceTitle = $othersTitle.replaceTitle}
                            {if $replaceTitle}
                                {block name="frontend_advisor_listing_title_others"}
                                    <span class="main-title--others">
                                    {$advisor['remainingPostsTitle']}
                                </span>
                                {/block}
                            {/if}

                            {block name="frontend_advisor_listing_title_filtered"}
                                <span class="main-title--filtered{if $replaceTitle} advisor--is-hidden{/if}">
                                {$advisor['listingTitleFiltered']|truncate:80}
                            </span>
                            {/block}
                        </div>
                    {/block}

                    {block name="frontend_advisor_listing_actions"}
                        {if !$theme.infiniteScrolling}
                            {include file="frontend/advisor/result/listing_actions.tpl" baseUrl=$advisorUrl}
                        {/if}
                    {/block}

                    {block name="frontend_advisor_listing_ct"}
                        <div class="listing-ct--advisor-result"
                             data-advisor-listing="true"
                             data-listingContainerSelector=".listing-ct--advisor-result"
                            {if $theme.infiniteScrolling}
                             data-listing-url="{url controller=advisor action=ajaxResult advisorParams=$advisorParams}"
                             data-infinite-scrolling="true"
                             data-filter-form="true"
                             data-load-facets="false"
                             data-instant-filter-result="false"
                             data-is-in-sidebar="false"
                             data-listing-actions="true"
                             data-loadPreviousSnippet="{s name="ListingActionsLoadPrevious" namespace="frontend/listing/listing"}{/s}"
                             data-loadMoreSnippet="{s name="ListingActionsLoadMore" namespace="frontend/listing/listing"}{/s}"
                             data-categoryId="1"
                             data-pages="{$pages}"
                             data-threshold="{$theme.infiniteThreshold}"
                            {/if}>
                            {$othersTitleFound = false}
                            {foreach $advisor['result'] as $product}

                                {block name="frontend_advisor_listing_others_title"}
                                    {include file="frontend/advisor/result/others.tpl" scope='parent'}
                                {/block}

                                {* Single product box *}
                                {block name="frontend_advisor_listing_article"}
                                    {include file="frontend/listing/box_article.tpl" sArticle=$product productBoxLayout=$advisor['listingLayout']}
                                {/block}
                            {/foreach}
                        </div>
                    {/block}

                    {block name="frontend_advisor_listing_actions_bottom"}
                        {if !$theme.infiniteScrolling}
                            {include file="frontend/advisor/result/listing_actions.tpl" baseUrl=$advisorUrl}
                        {/if}
                    {/block}

                </div>
            {/if}
        {/block}
    </div>
{/block}
