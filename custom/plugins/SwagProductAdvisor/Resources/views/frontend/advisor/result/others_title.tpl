{block name="frontend_advisor_listing_others_title_ct"}
    <div class="advisor--listing-title advisor--others-title">
        {block name="frontend_advisor_listing_others_title_inner"}
            <div class="others-title--inner">
                {$advisor['remainingPostsTitle']|truncate:80}
            </div>
        {/block}
    </div>
{/block}