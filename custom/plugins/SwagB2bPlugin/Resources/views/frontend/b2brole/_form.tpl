{namespace name=frontend/plugins/b2b_debtor_plugin}

{foreach $errors as $error}
    <div class="modal--errors error--list">
        {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
    </div>
{/foreach}

<div class="block-group b2b--form">
    <div class="block box--role-label">
        {s name="RoleName"}Rolename{/s}: *
    </div>
    <div class="block box--role-input">
        <input type="text" name="name" value="{$role->name}" placeholder="{s name="RoleName"}Rolename{/s}">
    </div>
</div>