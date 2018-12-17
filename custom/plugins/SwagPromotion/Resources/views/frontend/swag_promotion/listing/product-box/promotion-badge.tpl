{$promotionStruct = $sArticle.attributes.promotion}
{if !empty($promotionStruct->promotions)}
    {$hasBadge = false}
    {foreach item=prom from=$promotionStruct->promotions}
        {if $prom->showBadge && $hasBadge == false}
            {$hasBadge = true}
            <div class="product--badge badge--recommend promotionBadge" title="{$prom->badgeText|strip_tags}">
                {if $prom->badgeText}
                    {$prom->badgeText|strip_tags}
                {else}
                    {s name="promotionBadge" namespace="frontend/swag_promotion/main"}{/s}
                {/if}
            </div>
        {/if}
    {/foreach}
{/if}
