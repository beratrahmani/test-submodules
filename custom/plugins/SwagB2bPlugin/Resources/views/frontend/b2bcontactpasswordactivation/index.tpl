{extends file='frontend/index/index.tpl'}

{namespace name=frontend/plugins/b2b_debtor_plugin}

{block name='frontend_index_content_left'}{/block}

{block name='frontend_index_content'}
    <div class="content account--password-reset">
        <div class="password-reset--content panel has--border is--rounded">
            <h2 class="password-reset--title panel--title is--underline">{s name="PasswordActivation"}Password Activation{/s}</h2>
            <div class="b2b--password-activation--messages">
                {if $b2bErrors}
                    {foreach $b2bErrors as $error}
                        {include file="frontend/_includes/messages.tpl" type="error" content={$error|snippet:$error:"frontend/plugins/b2b_debtor_plugin"}}
                    {/foreach}
                {else}
                    {if $activation && !$activation->id}
                        {include file="frontend/_includes/messages.tpl" type="success" content={'PasswordActivationSuccessful'|snippet:'PasswordActivationSuccessful':"frontend/plugins/b2b_debtor_plugin"}}
                    {else}
                        {include file="frontend/_includes/messages.tpl" type="info" content={"PasswordActivationInfo"|snippet:"PasswordActivationInfo":"frontend/plugins/b2b_debtor_plugin"}}
                    {/if}
                {/if}

            </div>

            <form action="{url controller=b2bcontactpasswordactivation}" method="post">
                <input type="hidden" name="hash" value="{$hash|escape}">
                <div class="password-reset--form-content panel--body is--wide is--align-center">
                    <div class="register--password">
                        <input type="password" name="passwordNew" value="" placeholder="{s name="Password"}Password{/s}  *">
                    </div>

                    <div class="register--password">
                        <input type="password" name="passwordRepeat"  value="" placeholder="{s name="Confirm"}Confirm{/s} *">
                    </div>

                    <div class="password-reset--form-actions panel--actions is--full is--align-center">
                        <button class="btn is--primary is--center" type="submit">
                            {s name="Save"}Save{/s}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/block}