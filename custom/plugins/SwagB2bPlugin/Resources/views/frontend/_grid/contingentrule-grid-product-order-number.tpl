{namespace name=frontend/plugins/b2b_debtor_plugin}

<td>
    <ul class="list--unstyled">
        <li>
            {$ruleSippetKey = $row->type|cat:'Error'}
            {$ruleText = $ruleSippetKey|snippet:$ruleSippetKey:'frontend/plugins/b2b_debtor_plugin'}
            <b>{$ruleText|replace:'%2$s':$row->productOrderNumber}</b>
        </li>
    </ul>
</td>
