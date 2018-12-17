{strip}

{* Label Translation Snippets *}
{foreach $labels as $key => $label}

    {if !$label|is_array}
        {continue}
    {/if}

    {* Month label is spereated *}
    {$month = $label.month|snippet:$label.month:"frontend/plugins/b2b_debtor_plugin"}
    {$year = $label.year}

    {$labels[$key] = $month|cat:', '|cat:$year}

{/foreach}

{* Data Translation Snippets *}
{foreach $data as $rawKey => $rawAxis}
    {$rawLabel = $rawKey}
    {$outputLabel = $rawLabel|snippet:"_statistics_{$rawLabel}":"frontend/plugins/b2b_debtor_plugin"}

    {$outputData[$outputLabel] = $rawAxis}
{/foreach}

{$data = ['labels' => $labels, 'data' => $outputData]}

{json_encode($data)}
{/strip}