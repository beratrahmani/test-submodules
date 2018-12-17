{namespace name=frontend/plugins/b2b_debtor_plugin}

{foreach $errors as $error}
    <div class="modal--errors error--list">
        {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
    </div>
{/foreach}

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="ContingentGroupName"}Name{/s}: *
    </div>
    <div class="block box--input is--full">
        <input type="text" name="name" value="{$contingentGroup->name}" placeholder="{s name="ContingentGroupName"}Name{/s}">
    </div>
</div>
<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="ContingentGroupDescription"}Description{/s}:
    </div>
    <div class="block box--input is--full">
        <input type="text" name="description" value="{$contingentGroup->description}" placeholder="{s name="ContingentGroupDescription"}Description{/s}">
    </div>
</div>
