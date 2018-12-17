{namespace name=frontend/plugins/b2b_debtor_plugin}

{foreach $errors as $error}
    <div class="modal--errors error--list">
        {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
    </div>
{/foreach}

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="Salutation"}Salutation{/s}: *
    </div>
    <div class="block box--input is--full">
        <div class="select-field">
            {$salutations = ','|explode:{config name=shopsalutations}}
            <select name="salutation">
                {foreach $salutations as $salutation}
                    {$salutationSnippet = $salutation|ucfirst}
                    <option value="{$salutation}" {if $salutation === $contact->salutation}selected="selected"{/if}>
                        {$salutationSnippet|snippet:$salutationSnippet:'frontend/plugins/b2b_debtor_plugin'}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="FirstName"}Firstname{/s}: *
    </div>
    <div class="block box--input is--full">
        <input type="text" name="firstName" value="{$contact->firstName}" placeholder="{s name="FirstName"}Firstname{/s}">
    </div>
</div>

<div class="block-group  b2b--form">
    <div class="block box--label is--full">
        {s name="SurName"}Surname{/s}: *
    </div>
    <div class="block box--input is--full">
        <input type="text" name="lastName" value="{$contact->lastName}" placeholder="{s name="SurName"}Surname{/s}">
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="Email"}E-Mail{/s}: *
    </div>
    <div class="block box--input is--full">
        <input type="email" name="email" value="{$contact->email}" placeholder="{s name="Email"}E-Mail{/s}">
    </div>
</div>

<div class="block-group  b2b--form">
    <div class="block box--label is--full">
        {s name="Department"}Department{/s}:
    </div>
    <div class="block box--input is--full">
        <input type="text" name="department" value="{$contact->department}" placeholder="{s name="Department"}Department{/s}">
    </div>
</div>

<div class="block-group b2b--form b2b--password {if $type=="create"}is--hidden{/if}">
    <div class="block box--label is--full">
        {s name="Password"}Password{/s}: *
    </div>
    <div class="block box--input is--full">
        <input type="password" name="passwordNew" class="b2b--input-password" value="" placeholder="{s name="Password"}Password{/s}">
    </div>
</div>

<div class="block-group b2b--form b2b--password {if $type=="create"}is--hidden{/if}">
    <div class="block box--label is--full">
        {s name="Confirm"}Confirm{/s}: *
    </div>
    <div class="block box--input is--full">
        <input type="password" name="passwordRepeat" class="b2b--input-password" value="" placeholder="{s name="ConfirmPassword"}Confirm your password{/s}">
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="PasswordActivation"}Password activation{/s}:
    </div>
    <div class="block box--input is--full">
        <span class="checkbox">
            <input type="checkbox" name="passwordActivation" class="b2b--password-activation" {if $type=="create"}checked{/if} value="1">
            <span class="checkbox--state"></span>
        </span>
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="Active"}Active{/s}:
    </div>
    <div class="block box--input is--full">
        <span class="checkbox">
            <input type="checkbox" name="active" value="1" {if $contact->active || $isNew}checked="checked"{/if}>
            <span class="checkbox--state"></span>
        </span>
    </div>
</div>