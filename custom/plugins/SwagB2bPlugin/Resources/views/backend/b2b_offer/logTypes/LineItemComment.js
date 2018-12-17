//{namespace name=backend/plugins/b2b_debtor_plugin}
{literal}
'<tpl for="authorIdentity">',
'<tpl if="this.isBackend(isBackend) == true">',
'<li style="border: 1px solid #2C81D3; float: right; border-left: 30px solid #2C81D3; color: #5f7285; background: #fff; position: relative; padding: 10px; margin-bottom: 5px;">',
'<tpl else>',
'<li style="border: 1px solid #A3ADB4; float: left; border-left: 30px solid #A3ADB4; color: #5f7285; background: #fff; position: relative; padding: 10px; margin-bottom: 5px;">',
'</tpl>',
'</tpl>',

    '<span style="position: absolute; left: -19px; border-radius: 3px; width: 15px; height: 15px; color: #fff; font-size: 20px;">&#x2139;</span>',

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


    '<tpl for="logValue">',
    {/literal}
    '{s name="OrderItemCommentChange"}The comment of an order item has been changed to{/s}:',
    {literal}

    '<br>',
    '<br>',

    '<h5>',
    '{newValue}',
    '</h5>',
    '</tpl>',

'<br>',

    '<table class="table--unstyled" style="margin-top: 5px;">',
        '<tr>',
            '<td>',
                {/literal}
                '{s name="Product"}Product{/s}:&nbsp;',
                {literal}
            '</td>',
            '<td>{productName}</td>',
        '</tr>',
        '<tr>',
            '<td>',
                {/literal}
                '{s name="Productnumber"}Productnumber{/s}:&nbsp;',
                {literal}
            '</td>',
            '<td>{orderNumber}</td>',
        '</tr>',
    '</table>',
'</li>',
'<div style="clear: both"></div>',
{/literal}
