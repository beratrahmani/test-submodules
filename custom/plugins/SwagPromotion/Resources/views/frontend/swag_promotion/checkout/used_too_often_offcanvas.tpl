{foreach $promotionsUsedTooOften as $promotionUsedTooOften}
    {$content = "{s namespace='frontend/swag_promotion/main' name='usedPromotions'}The campaign '{$promotionUsedTooOften->name}' is not available for you anymore! (Used {if $promotionUsedTooOften->maxUsage == 1}once{else}{$promotionUsedTooOften->maxUsage} times{/if}){/s}"}
    {include file="frontend/_includes/messages.tpl" type="info"}
{/foreach}
