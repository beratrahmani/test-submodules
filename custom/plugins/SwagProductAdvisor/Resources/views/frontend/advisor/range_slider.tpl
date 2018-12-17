{namespace name="frontend/listing/listing_actions"}

{block name="frontend_advisor_sidebar_price_slider_config"}
    {$startMin = $question['min']}
    {$startMax = $question['max']}

    {if $question['answered']}
        {if $question['selectedMin']}
            {$minAnswered = true}
            {$startMin = $question['selectedMin']}
        {/if}
        {if $question['selectedMax']}
            {$maxAnswered = true}
            {$startMax = $question['selectedMax']}
        {/if}
    {/if}

    {$rangeMin = $question['min']}
    {$rangeMax = $question['max']}
    {$roundPretty = 'true'}
    {$format = '0'|currency}
    {$stepCount = 100}
    {$stepCurve = 'linear'}
{/block}

{* Contains the price-slider *}
{block name="frontend_advisor_sidebar_answer_price_slider"}
    <div class="advisor--range-slider{if $question['required']} required{/if}"
        data-range-slider="true"
        data-roundPretty="{$roundPretty}"
        data-labelFormat="{$format}"
        data-stepCount="{$stepCount}"
        data-stepCurve="{$stepCurve}"
        data-startMin="{$startMin}"
        data-startMax="{$startMax}"
        data-rangeMin="{$rangeMin}"
        data-rangeMax="{$rangeMax}"
        {if $question['minCss']}data-handleMinCls="is--min {$question['minCss']|escapeHtmlAttr}"{/if}
        {if $question['maxCss']}data-handleMaxCls="is--max {$question['maxCss']|escapeHtmlAttr}"{/if}>

        {block name="frontend_advisor_sidebar_price_slider_min"}
            <input type="hidden"
                class="advisor--price-min"
                id="min"
                name="q{$question['id']|escapeHtml}_values_min"
                data-range-input="min"
                data-answer="answer{$question['id']|escapeHtml}"
                value="{$startMin|escapeHtmlAttr}"/>
        {/block}

        {block name="frontend_advisor_sidebar_price_slider_max"}
            <input type="hidden"
                class="advisor--price-max{if $question['maxCss']} {$question['maxCss']|escapeHtmlAttr}{/if}"
                id="max"
                name="q{$question['id']|escapeHtml}_values_max"
                data-range-input="max"
                data-answer="answer{$question['id']}"
                value="{$startMax|escapeHtmlAttr}"/>
        {/block}

        {block name="frontend_advisor_sidebar_price_slider_info"}
            <div class="filter-panel--range-info">
                {block name="frontend_advisor_sidebar_price_slider_min_label"}
                    <span class="range-info--min">
                        {s name="ListingFilterRangeFrom"}{/s}
                    </span>

                    <label class="range-info--label"
                        for="min"
                        data-range-label="min">
                        {$startMin}
                    </label>
                {/block}

                {block name="frontend_advisor_sidebar_price_slider_max_label"}
                    <span class="range-info--max">
                        {s name="ListingFilterRangeTo"}{/s}
                    </span>

                    <label class="range-info--label"
                        for="max"
                        data-range-label="max">
                        {$startMax}
                    </label>
                {/block}
            </div>
        {/block}
    </div>
{/block}