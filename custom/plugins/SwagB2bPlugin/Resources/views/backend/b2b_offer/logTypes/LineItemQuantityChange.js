//{namespace name=backend/plugins/b2b_debtor_plugin}
{literal}
'<tpl for="authorIdentity">',
'<tpl if="this.isBackend(isBackend) == true">',
'<li style="border: 1px solid #3BAAFA; float: right; border-left: 30px solid #3BAAFA; color: #5f7285; background: #fff; position: relative; padding: 10px; margin-bottom: 5px;">',
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

{/literal}
'{s name="OrderItemQuantityChange"}The quantity of an item has been changed.{/s}',
{literal}

'<br>',
'<br>',

'<table class="table--unstyled" style="margin-top: 5px;">',
'<tpl for="logValue">',
    '<tr>',
        {/literal}
        '<td>{s name="Product"}Product{/s}:</td>',
        {literal}
        '<td>{productName}</td>',
    '</tr>',
    '<tr>',
        {/literal}
        '<td>{s name="Productnumber"}Productnumber{/s}:</td>',
        {literal}
        '<td>{orderNumber}</td>',
    '</tr>',

    '<tr>',
        {/literal}
        '<td>{s name="OrderQuantityOld"}Old quantity{/s}:</td>',
        {literal}
        '<td>{oldValue}</td>',
    '</tr>',
    '<tr>',
        {/literal}
        '<td>{s name="OrderQuantityNew"}New quantity{/s}:</td>',
        {literal}
            '<td>{newValue}</td>',
        '</tr>',

    '</tpl>',
    '</table>',
'</li>',
'<div style="clear: both"></div>',
{/literal}