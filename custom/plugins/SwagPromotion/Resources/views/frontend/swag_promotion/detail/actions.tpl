{namespace name="frontend/swag_promotion/main"}

{if $sArticle.attributes.promotion}
    {$indexNumber = 1}
    {foreach $sArticle.attributes.promotion->promotions as $promotion}
        {if $promotion->description != ""}
            {*create a footnote if we have a validTo date*}
            {if $promotion->validTo}
                {$index = "<span class='promotion--index'><sup>*{$indexNumber}<sup><span>"}
            {/if}

            {*short description with index number for footnote*}
            {$description = "<div class='promotion--description'><b>{s name="promotionAttention" namespace="frontend/swag_promotion/main"}Attention:{/s}</b> {eval var=$promotion->description}{$index}</div>"}
            {$detailDescription = {eval var=$promotion->detailDescription}}

            {*create content for modal box and off canvas if we have a detailed description*}
            {if $promotion->detailDescription != ""}
                {*promotion short info box. shows detailed info on click in modal box (m, l, xl) or off canvas (xs, s) *}
                {$arrow = '<i class="icon--arrow-right promotion--is-right"></i>'}
                {block name="frontend_detail_index_actions_promotion_short_description"}
                    <div class="promotion--description-box"
                        {*modal box content*}
                         data-content="{$detailDescription|escape:'htmlall'}"
                         data-title="{$promotion->name}">
                        {include file="frontend/_includes/messages.tpl" icon="icon--percent2" type="promotion" content="{$description}{$arrow}"}
                    </div>
                {/block}

                {*off canvas content*}
                {block name="frontend_detail_index_actions_promotion_detailed_description_offcanvas"}
                    <div class="promotion--detail-offcanvas">
                        <div class="buttons--off-canvas">
                            <a href="#" class="close--off-canvas">
                                <i class="icon--arrow-left"></i> {s name="OffcanvasCloseMenu" namespace="frontend/detail/description"}{/s}
                            </a>
                        </div>
                        <div class="promotion--content-description">
                            <div class="promotion--content-title">
                                {$promotion->name}
                            </div>
                            <div>
                                {eval var=$promotion->detailDescription}
                            </div>
                        </div>
                    </div>
                {/block}
            {else}
                {block name="frontend_detail_index_actions_promotion_short_description"}
                    {include file="frontend/_includes/messages.tpl" icon="icon--percent2" type="success" content="{$description}"}
                {/block}
            {/if}

            {*create footnote for promotion if index is set*}
            {if $index}
                {$footNoteArray[$indexNumber] = "<span class='promotion--footnote-index'><sup>*{$indexNumber}</sup>{s name="promotionValidUntil"}{/s}{$promotion->validTo|date_format:"{s name="promotionValidUntilDateFormat"}{/s}"}</span></br>"}
                {$indexNumber = $indexNumber + 1}
            {/if}
        {/if}
    {/foreach}
    {block name="frontend_detail_index_actions_promotion_footnotes"}
        {foreach $footNoteArray as $footNote}
            <div class="promotion--description-footnote">
                {$footNote}
            </div>
        {/foreach}
    {/block}
{/if}
