{if !$advisor['result'] && !$advisor['topHit']}
    {include file="frontend/advisor/result/error.tpl"}
{else}
    {include file="frontend/advisor/result/listing.tpl"}
{/if}