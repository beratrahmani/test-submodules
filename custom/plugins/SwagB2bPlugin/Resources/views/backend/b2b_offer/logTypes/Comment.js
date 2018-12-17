//{namespace name=backend/plugins/b2b_debtor_plugin}
{literal}
'<tpl for="authorIdentity">',

    '<tpl if="this.isBackend(isBackend) == true">',

    '<li style="width: 100%; float: right;position: relative; padding: 5px 10px 5px 40px;">',
    '<h5 style="margin-bottom: 4px; word-break: break-word; color: #fff; background: #3BAAFA; padding: 5px; border: 1px solid #3BAAFA; border-radius: 4px !important;">',

    '<tpl else>',

    '<li style="width: 100%; float: left; position: relative; padding: 5px 40px 5px 10px;">',
    '<h5 style="margin-bottom: 4px; word-break: break-word; color: #fff; background: #A3ADB4; padding: 5px; border: 1px solid #A3ADB4; border-radius: 4px !important;">',

    '</tpl>',
'</tpl>',

'<tpl for="logValue">',

'{newValue}',
'</h5>',

'</tpl>',

    '<tpl if="this.isSameType(values,parent[xindex+1]) === false">',

        '<span style="color: #444;">',
        '<tpl for="eventDate">',
        '{[this.getDate(values)]}',
        '</tpl>',
        ' &middot; ',
        '<tpl for="authorIdentity">',
        '{firstName} {lastName}',
        '</tpl>',
        '</span>',
    '</tpl>',
'</li>',
'<div style="clear: both"></div>',
{/literal}
