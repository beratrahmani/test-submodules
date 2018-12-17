{block name='frontend_liveshopping_counter_inner'}
    {* Days *}
    {block name="frontend_liveshopping_counter_day"}
        <span class="liveshopping--days counter--number">{$liveShopping.remaining.days}</span>
        <span class="counter--text">{s name="sLiveDaysShort" namespace="frontend/live_shopping/main"}{/s}</span>
    {/block}

    {* Hours *}
    {block name="frontend_liveshopping_counter_hour"}
        <span class="liveshopping--hours counter--number">{$liveShopping.remaining.hours}</span>
        <span class="counter--text">{s name="sLiveHoursShort" namespace="frontend/live_shopping/main"}{/s}</span>
    {/block}

    {* Minutes *}
    {block name="frontend_liveshopping_counter_min"}
        <span class="liveshopping--minutes counter--number">{$liveShopping.remaining.minutes}</span>
        <span class="counter--text">{s name="sLiveMinutesShort" namespace="frontend/live_shopping/main"}{/s}</span>
    {/block}

    {* Seconds *}
    {block name="frontend_liveshopping_content_number"}
        <span class="liveshopping--seconds counter--number">{$liveShopping.remaining.seconds}</span>
        <span class="counter--text">{s name="sLiveSecondsShort" namespace="frontend/live_shopping/main"}{/s}</span>
    {/block}
{/block}
