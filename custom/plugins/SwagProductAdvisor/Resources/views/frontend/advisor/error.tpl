{$list = []}
{block name="frontend_advisor_result_error_list"}
    {foreach $advisorErrors as $errorType => $error}
        {foreach $error as $errorVarKey => $errorVar}
            {assign var=$errorVarKey value=$errorVar}
        {/foreach}

        {$list[] = {include file="string:{""|snippet:$errorType:"frontend/advisor/error"}"}}
    {/foreach}
{/block}

{block name="frontend_advisor_result_error_message"}
    {include file="frontend/_includes/messages.tpl" type="error" list=$list}
{/block}

{if $advisor['mode'] === 'wizard_mode'}
    {block name="frontend_advisor_result_error_button"}
        <a class="advisor--last-question btn is--primary is--icon-left"
           title="{s name="lastQuestionText" namespace="frontend/advisor/error"}Back to the last question{/s}"
           href="{url controller=advisor action=index advisorId=$advisor['id']}">
            <i class="icon--arrow-left"></i>
            {s name="lastQuestionText" namespace="frontend/advisor/error"}Back to the last question{/s}
        </a>
    {/block}
{/if}