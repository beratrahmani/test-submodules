{block name='frontend_liveshopping_charge_counter_inner'}

    {* Charge icon *}
    {block name='frontend_liveshopping_charge_counter_icon'}
        <span class="discount--charge-icon">
			{if $liveShopping.type === 2}
                <i class="icon--minus3"></i>
            {elseif $liveShopping.type === 3}
				<i class="icon--plus3"></i>
            {/if}
		</span>
    {/block}

    {* Charge price *}
    {block name='frontend_liveshopping_charge_counter_price'}
        <span class="discount--charge-price">
			{$liveShopping.perMinute|currency}
		</span>
    {/block}

    {* Charge price spacer *}
    {block name='frontend_liveshopping_charge_counter_spacer'}
        <span class="counter--text">
			{s name='sLivePriceSpacer' namespace="frontend/live_shopping/main"}{/s}
		</span>
    {/block}

    {* Charge price seconds *}
    {block name='frontend_liveshopping_charge_counter_seconds'}
        <span class="liveshopping--seconds">
			{$liveShopping.remaining.seconds}
		</span>
        <span class="counter--text">
			{s name="sLiveSecondsShort" namespace="frontend/live_shopping/main"}{/s}
		</span>
    {/block}
{/block}
