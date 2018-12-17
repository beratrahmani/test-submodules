{namespace name="frontend/detail/abo_commerce_detail"}

{block name='abo_commerce_abo_selection_delivery_duration'}
    <div class="abo--duration-interval-label">
        {block name='abo_commerce_abo_selection_delivery_duration_label'}
            {if $aboCommerce.endlessSubscription }
                <label class="duration-interval--label">{s name="AboCommerceEndlessSubscription"}{/s}</label>
            {else}
                <label class="duration-interval--label" for="duration-interval">{s name="AboCommerceIntervalSelectDuration"}{/s}</label>
            {/if}
        {/block}
    </div>
{/block}

{block name='abo_commerce_abo_selection_delivery_duration_select'}
    {if $aboCommerce.endlessSubscription }
        <div class="abo--cancel-info">
            {if $aboCommerce.directTermination eq true}
                {s name="AboCommerceFlexibleTermination"}{/s}
            {else}
                {s name="AboCommerceNoticePeriod"}{/s}
                {if $aboCommerce.periodOfNoticeUnit eq 'weeks'}
                    {s name="AboCommerceNoticePeriodWeeks"}{/s}
                {else}
                    {s name="AboCommerceNoticePeriodMonths"}{/s}
                {/if}
            {/if}
        </div>
    {else}
        <div class="abo--duration-interval-select select-field">
            <select name="duration-interval" class="abo--duration-interval">
                {for $durationInterval=$aboCommerce.minDuration to $aboCommerce.maxDuration}
                    {block name="abo_commerce_abo_selection_delivery_duration_select_option"}
                        <option value="{$durationInterval}">
                            {$durationInterval}&nbsp;
                            {if $aboCommerce.durationUnit == "weeks"}
                                {s name="AboCommerceIntervalSelectWeeks"}{/s}
                            {else}
                                {s name="AboCommerceIntervalSelectMonths"}{/s}
                            {/if}
                        </option>
                    {/block}
                {/for}
            </select>
        </div>
    {/if}

{/block}
