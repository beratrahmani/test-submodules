// {namespace name=backend/customer/view/main}
// {block name="backend/customer/model/quick_view/fields"}
// {$smarty.block.parent}
{ name: 'b2bIsSalesRepresentative', type: 'bool', mapping: function(value) {
    if (value.attribute) {
        return value.attribute.b2bIsSalesRepresentative;
    }

    return 0;
}},
// {/block}
