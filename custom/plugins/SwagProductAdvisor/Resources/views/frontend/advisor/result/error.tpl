<div id="advisor-listing--container" class="advisor--listing-error">
    {block name="frontend_advisor_result_no_results"}
        {block name="frontend_advisor_result_no_results_title"}
            <div class="advisor--no-results-title">
                {s name="noResultTitle" namespace="frontend/advisor/error"}No results were found!{/s}
            </div>
        {/block}

        {block name="frontend_advisor_result_no_results_text"}
            <p class="advisor--no-results-text">
                {s name="noResultText" namespace="frontend/advisor/error"}Sadly there are no matching results for your selection.{/s}
            </p>
        {/block}

        {if $advisor['mode'] === 'wizard_mode'}
            {block name="frontend_advisor_result_no_results_button"}
                <div class="advisor--no-results-button">
                    <a class="btn is--primary is--icon-left no-results-button--return" href="{$advisor['lastQuestionUrl']}">
                        {s name="LastQuestionBtnText" namespace="frontend/advisor/main"}Return to last question{/s}
                        <i class="icon--arrow-left"></i>
                    </a>
                </div>
            {/block}
        {/if}
    {/block}
</div>