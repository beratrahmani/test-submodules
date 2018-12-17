//{namespace name=backend/plugins/b2b_debtor_plugin}
{literal}
'<tpl for="authorIdentity">',
    '<tpl if="this.isEmpty(newVlaue) != true">',
    '<tpl if="this.isBackend(isBackend) == true">',
    '<li style="border: 1px solid #3BAAFA; float: right; border-left: 30px solid #3BAAFA; color: #5f7285; background: #fff; position: relative; padding: 10px; margin-bottom: 5px;">',
    '<tpl else>',
    '<li style="border: 1px solid #A3ADB4; float: left; border-left: 30px solid #A3ADB4; color: #5f7285; background: #fff; position: relative; padding: 10px; margin-bottom: 5px;">',
    '</tpl>',

    '<span style="position: absolute; left: -23px; border-radius: 3px; width: 15px; height: 15px; color: #fff; font-size: 20px;">X</span>',

    '<tpl else>',
    '<tpl if="this.isBackend(isBackend) == true">',
    '<li style="border: 1px solid #3BAAFA; float: right; border-left: 30px solid #3BAAFA; color: #5f7285; background: #fff; position: relative; padding: 10px; margin-bottom: 5px;">',

    '<span style="position: absolute; left: -19px; border-radius: 3px; width: 15px; height: 15px; color: #fff; font-size: 20px;">&#x2139;</span>',
    '<tpl else>',
    '<li style="border: 1px solid #A3ADB4; float: left; border-left: 30px solid #A3ADB4; color: #5f7285; background: #fff; position: relative; padding: 10px; margin-bottom: 5px;">',

    '<span style="position: absolute; left: -23px; border-radius: 3px; width: 15px; height: 15px; color: #fff; font-size: 20px;">&check;</span>',
    '</tpl>',
    '</tpl>',
'</tpl>',

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
    '<tpl if="newValue == \'0\'">',
    {/literal}
    '{s name="DiscountDeleted"}The discount has been deleted.{/s}',
    {literal}
    '<tpl elseif="this.isEmpty(oldValue) == true">',
    {/literal}
    '{s name="DiscountAdded"}A discount was added.{/s}',
    {literal}
    '<tpl elseif="this.isEmpty(newValue) != true">',
    {/literal}
    '{s name="DiscountChanged"}The discount has been changed to{/s}:',
    {literal}
    '</tpl>',


    '<tpl if="newValue != \'0\'">',

    '<br>',
    '<br>',

    '<h3>',
    '{newValue}',
    {/literal}
        '{b2b_currency_symbol}',
    {literal}
    '</h3>',

    '<br>',

    '<tpl if="oldValue != \'0\'">',

    '<table class="table--unstyled">',
        '<tr>',
            '<td>',
                {/literal}
                '{s name="OldDiscount"}Old discount{/s}:&nbsp;',
                {literal}
            '</td>',
            '<td>',
            '{oldValue}',
            {/literal}
                '{b2b_currency_symbol}',
            {literal}
            '</td>',
        '</tr>',
    '</table>',

'</tpl>',
'</tpl>',
'</tpl>',
'</li>',
'<div style="clear: both"></div>',
{/literal}