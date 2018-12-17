{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="frontend/_base/index.tpl"}

{block name="frontend_index_content_b2b"}
    <div class="b2b--account">
        <h1>{s name="MyAccount"}My account{/s}</h1>
        {if $changeMessage}
            {include file='frontend/_includes/messages.tpl' type=$changeMessage[0] content=$changeMessage[1]|snippet:$changeMessage[1]:'frontend/plugins/b2b_debtor_plugin'}
        {/if}

        <div class="block-group">
            <div class="block block-avatar">
                <div class="block-avatar-image">
                    {if $avatar}
                        <img src="{media path=$avatar}" alt="{$identity->firstName} {$identity->lastName}" >
                    {else}
                        <img src="{gravatar_url email=$identity->email}" alt="{$identity->firstName} {$identity->lastName}" />
                    {/if}
                    <div class="block-avatar-edit">
                        <form class="form--upload b2b--upload-form" method="post" action="{url action=processUpload}" enctype="multipart/form-data" data-url="{url action=processUpload}">
                            <label for="file-avatar">
                                {s name="ChangeProfilePicture"}Change profile picture{/s}
                            </label>

                            <input accept=".png, .jpg, .jpeg, .ico, .gif, .bmp" class="input--file is--auto-submit" type="file" name="uploadedFile" id="file-avatar" />
                        </form>
                    </div>
                </div>
                <div class="panel panel--options has--border is--rounded">
                    <div class="panel--title is--underline">
                        {s name="Actions"}Actions{/s}
                    </div>
                    <div class="panel--body">
                        <ul class="list--options">
                            {if !$isSalesRep}
                                {include file="frontend/b2baccount/client_actions.tpl"}
                            {/if}
                            <li>
                                <a title="{s name="Logout"}Logout{/s}" href="{url controller=account action=logout}">
                                    {s name="Logout"}Logout{/s}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
            <div class="block block-masterdata">

                <div class="panel panel--masterdata has--border is--rounded">
                    <div class="panel--title is--underline">
                        {s name="MasterData"}Master data{/s}
                    </div>
                    <div class="panel--body">
                        <ul class="list--masterdata">

                            {if $identity->debtor}
                                <li><span>{s name="AccountLevel"}Account level{/s}</span> {s name="Contact"}Contact{/s}</li>
                                <li><span>{s name="MyDebtor"}My debtor{/s}</span> {$identity->debtor->firstName} {$identity->debtor->lastName}</li>
                            {elseif $isSalesRep}
                                <li><span>{s name="AccountLevel"}Account level{/s}</span> {s name="SalesRepresentative"}Sales representative{/s}</li>
                            {else}
                                <li><span>{s name="AccountLevel"}Account level{/s}</span> {s name="Debtor"}Debtor{/s}</li>
                            {/if}

                            <li>
                                <span>{s name="Salutation"}Salutation{/s}</span>
                                {$salutationSnippet = $identity->salutation|ucfirst}
                                {$salutationSnippet|snippet:$salutationSnippet:'frontend/plugins/b2b_debtor_plugin'}
                            </li>
                            <li><span>{s name="Name"}Name{/s}</span> {$identity->firstName} {$identity->lastName}</li>
                            <li><span>{s name="Email"}E-Mail{/s}</span> {$identity->email}</li>
                        </ul>
                    </div>
                </div>

                <form action='{url controller=b2baccount action=savePassword}' method='post'>
                    <div class="panel panel--password has--border is--rounded">

                        <div class="panel--title is--underline">
                            {s name="ChangePassword"}Change Password{/s}
                        </div>
                        <div class="panel--body">
                            <ul class="list--password">
                                <li><span>{s name="CurrentPassword"}Current password{/s} *</span> <input type='password' name="currentPassword" placeholder="{s name="CurrentPassword"}Current password{/s}"></li>
                                <li><span>{s name="NewPassword"}New password{/s} *</span> <input type='password' name="password" placeholder="{s name="NewPassword"}New password{/s}"></li>
                                <li><span>{s name="ConfirmPassword"}Confirm password{/s} *</span> <input type='password' name="passwordConfirmation" placeholder="{s name="ConfirmPassword"}Confirm password{/s}"></li>
                                <li class="is--right">
                                    <button type="submit" class="btn is--primary">{s name="SavePassword"}Save password{/s}</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
{/block}
