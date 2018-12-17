// {namespace name=backend/customer/view/main}
// {block name="backend/customer/model/quick_view/fields"}
// {$smarty.block.parent}
{ name: 'b2bIsDebtor', type: 'bool', mapping: function(value) {
    if (value.attribute) {
        return value.attribute.b2bIsDebtor;
    }

    return 0;
}},
// {/block}
