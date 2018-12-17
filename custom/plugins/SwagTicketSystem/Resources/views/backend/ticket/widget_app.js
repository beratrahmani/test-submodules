
//{block name="backend/index/application"}
//{$smarty.block.parent}

    //{include file="backend/ticket/model/employee.js"}
    //{include file="backend/ticket/model/status.js"}
    //{include file="backend/ticket/model/list.js"}

    //{include file="backend/ticket/store/employee.js"}
    //{include file="backend/ticket/store/status.js"}
    //{include file="backend/ticket/store/widget_list.js"}

    // WIDGET
    //{include file="backend/ticket/view/widget/list.js"}

//{/block}