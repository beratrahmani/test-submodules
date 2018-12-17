{namespace name=frontend/plugins/b2b_debtor_plugin}

<td>
    <ul class="list--unstyled">
        <li>
            {$ruleSippetKey = $row->type|cat:'Error'}
            {$ruleText = $ruleSippetKey|snippet:$ruleSippetKey:'frontend/plugins/b2b_debtor_plugin'}
            <b>{$ruleText|replace:'%2$s':$row->value|replace:'%1$s':{$row->timeRestriction|snippet:$row->timeRestriction:'frontend/plugins/b2b_debtor_plugin'}}</b>
        </li>
    </ul>
</td>