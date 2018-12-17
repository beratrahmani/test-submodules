//{namespace name=backend/plugins/b2b_debtor_plugin}
{literal}
'<tpl for="authorIdentity">',
'<tpl if="this.isBackend(isBackend) == true">',
'<li style="border: 1px solid #3BAAFA; float: right; border-left: 30px solid #3BAAFA; color: #5f7285; background: #fff; position: relative; padding: 10px; margin-bottom: 5px;">',
'<tpl else>',
'<li style="border: 1px solid #A3ADB4; float: left; border-left: 30px solid #A3ADB4; color: #5f7285; background: #fff; position: relative; padding: 10px; margin-bottom: 5px;">',
'</tpl>',
'</tpl>',

    '<span style="position: absolute; left: -23px; border-radius: 3px; width: 15px; height: 15px; color: #fff; font-size: 20px;">&#x26A0;</span>',

    '<span>',
        '<tpl for="eventDate">',
        '{[this.getDate(values)]}',
        '</tpl>',
        ' &middot; ',
        '<tpl for="authorIdentity">',
        '{firstName} {lastName}',
        '</tpl>',
    '</span>',

    '<br>',
    '<br>',

    {/literal}
    '{s name="StatusChanged"}A Status has changed{/s}:',
    {literal}

    '<br>',
    '<br>',

    '<table class="table--unstyled">',
        '<tpl for="logValue">',
        '<tr>',
            '<td>',
                {/literal}
                '{s name="OrderStatusOld"}Old status{/s}:&nbsp;',
                {literal}
            '</td>',
            '<td>',
                '{[this.getStatus(values.oldValue)]}',
            '</td>',
        '</tr>',
        '<tr>',
            '<td>',
                {/literal}
                '{s name="OrderStatusNew"}New status{/s}:&nbsp;',
                {literal}
            '</td>',
            '<td>',
                '{[this.getStatus(values.newValue)]}',
            '</td>',
        '</tr>',
        '</tpl>',
    '</table>',
'</li>',
'<div style="clear: both"></div>',
{/literal}