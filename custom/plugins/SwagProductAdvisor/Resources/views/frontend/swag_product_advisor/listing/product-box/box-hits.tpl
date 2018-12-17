{extends file="frontend/listing/product-box/box-basic.tpl"}

{* The template for the hits-template *}
{block name="frontend_listing_box_article_description"}
    {if $productBoxLayout == 'advisor-tophit'}
        {$smarty.block.parent}
    {/if}
    {block name="frontend_advisor_listing_hits_ct"}
        <div class="advisor--hits-ct">
           {$advisorAttribute = $product['attributes']['advisor']}

            {if $advisorAttribute->getMatches()}
                {block name="frontend_advisor_listing_hits_ct_matches"}
                    <div class="hits-ct--matches">
                        <ul class="advisor--matches-list advisor--list">
                            {foreach $advisorAttribute->getMatches() as $match}
                                {block name="frontend_advisor_listing_hits_ct_match"}
                                    <li class="matches-list--single-match list--single-property">
                                        <i class="icon--check advisor--icon"></i>
                                        <div class="single-match--text single-property--text">{$match['label']}</div>
                                    </li>
                                {/block}
                            {/foreach}
                        </ul>
                    </div>
                {/block}
            {/if}

            {if $advisorAttribute->getMisses()}
                {block name="frontend_advisor_listing_hits_ct_misses"}
                    <div class="hits-ct--misses">
                        {block name="frontend_listing_box_advisor_misses_ct"}{/block}
                    </div>
                {/block}
            {/if}
        </div>
    {/block}
{/block}