{extends file='parent:frontend/_includes/messages.tpl'}

{block name="frontend_global_messages_content"}

    {function b2bContentFunction}
        {if $b2bcontent['property']}
            {$b2bcontent['property']|snippet:$b2bcontent['property']:"frontend/plugins/b2b_debtor_plugin"} :
        {/if}

        {$errormessage = $b2bcontent['messageTemplate']|snippet:$b2bcontent['snippetKey']:"frontend/plugins/b2b_debtor_plugin"}

        {foreach $b2bcontent['parameters'] as $component => $route}
            {if is_string($route) && $route === ''}
                {continue}
            {/if}
            {$routeSnippetKey = 'errorMessage'|cat:ucfirst($route)}

            {$routeTranslated = $route}
            {if !is_numeric($route) && $b2bcontent['cause'] !== 'isProduct'}
                {$routeTranslated = $route|snippet:$routeSnippetKey:"frontend/plugins/b2b_debtor_plugin"}
            {/if}

            {$errormessage = str_replace($component, $routeTranslated, $errormessage)}
        {/foreach}

        {$errormessage}
    {/function}

    <div class="alert--content{if $isBold} is--strong{/if}">
        {if $content && !$list}
            {$content}
        {else}
            <ul class="alert--list">
                {foreach $list as $entry}
                    <li class="list--entry{if $entry@first} is--first{/if}{if $entry@last} is--last{/if}">{$entry}</li>
                {/foreach}
            </ul>
        {/if}

        {if $b2bcontent}
            {b2bContentFunction b2bcontent=$b2bcontent}
        {elseif $b2bContentList}
            <ul class="alert--list">
                {foreach $b2bContentList as $b2bcontent}
                    <li class="list--entry{if $b2bcontent@first} is--first{/if}{if $b2bcontent@last} is--last{/if}">
                        {if !is_array($b2bcontent)}
                            {$b2bcontent}
                        {else}
                            {b2bContentFunction b2bcontent=$b2bcontent}
                        {/if}
                    </li>
                {/foreach}
            </ul>
        {/if}
    </div>
{/block}
