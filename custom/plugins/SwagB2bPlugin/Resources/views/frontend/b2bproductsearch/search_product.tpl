{namespace name=frontend/plugins/b2b_debtor_plugin}
{strip}
<ul>
    {foreach $products as $number => $data}
    <li {if $data.max}data-max="{$data.max}"{/if} data-min="{$data.min}" data-step="{$data.step}">
        <span>
            {$number}
        </span>
        <div>
            {$data.name}
        </div>
    </li>
    {foreachelse}
        <li>
            <div>{s name="NoResultsFound"}No results found{/s}</div>
        </li>
    {/foreach}
</ul>
{/strip}