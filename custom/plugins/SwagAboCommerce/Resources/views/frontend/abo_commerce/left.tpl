{namespace name="frontend/abo_commerce/left"}

{block name="frontend_abo_abo_commerce_left"}
    {block name="frontend_abo_abo_commerce_left_main"}
        <div class="panel--body has--border abo--sidebar">

            {block name="frontend_abo_abo_commerce_left_main_headline"}
                <h2 class="sidebar--headline">{$aboCommerceSettings.sidebarHeadline}</h2>
            {/block}

            {block name="frontend_abo_abo_commerce_left_main_text"}
                <div class="sidebar--content">
                    {$aboCommerceSettings.sidebarText}
                </div>
            {/block}

            {block name="frontend_abo_abo_commerce_left_main_image"}
                <div class="sidebar--abo-image"></div>
            {/block}
        </div>
    {/block}

    {block name="frontend_abo_abo_commerce_left_sharing"}
        {if $aboCommerceSettings.sharingGoogle || $aboCommerceSettings.sharingFacebook || $aboCommerceSettings.sharingTwitter || $aboCommerceSettings.sharingMail}
            <div class="panel--body has--border abo--sidebar abo--share">
                {block name="frontend_abo_abo_commerce_left_sharing_headline"}
                    <h2 class="sidebar--headline">{s namespace="frontend/abo_commerce/right" name="AboCommerceSidebarShareWithFriends"}{/s}</h2>
                {/block}

                {block name="frontend_abo_abo_commerce_left_sharing_content"}
                    <div class="sidebar--content">
                        {if $aboCommerceSettings.sharingGoogle}
                            {block name="frontend_abo_abo_commerce_left_sharing_googleplus"}
                                <a class="share--google abo-sharing" href="https://plus.google.com/share?url={"{url action='index'}"|escape:url}" target="_blank" rel="nofollow" title="{"{s name="AboCommerceSidebarShareGooglePlus"}{/s}"|escape}"></a>
                            {/block}
                        {/if}

                        {if $aboCommerceSettings.sharingFacebook}
                            {block name="frontend_abo_abo_commerce_left_sharing_facebook"}
                                <a class="share--facebook abo-sharing" href="http://www.facebook.com/share.php?u={"{url action='index'}"|escape:url}" target="_blank" rel="nofollow" title="{"{s name="AboCommerceSidebarShareFacebook"}{/s}"|escape}"></a>
                            {/block}
                        {/if}

                        {if $aboCommerceSettings.sharingTwitter}
                            {block name="frontend_abo_abo_commerce_right_sharing_twitter"}
                                <a class="share--twitter abo-sharing" href="http://twitter.com/home?status={"{url action='index'}"|escape:url}" target="_blank" rel="nofollow" title="{"{s name="AboCommerceSidebarShareTwitter"}{/s}"|escape}"></a>
                            {/block}
                        {/if}

                        {if $aboCommerceSettings.sharingMail}
                            {block name="frontend_abo_abo_commerce_right_sharing_Mail"}
                                <a class="share--mail abo-sharing" href="mailto:?subject={"{s namespace="frontend/abo_commerce/right" name='AboCommerceSidebarMailSubject'}{/s}"|escape:url}&body={"{url action='index'}"|escape:url}" title="{"{s namespace="frontend/abo_commerce/right" name="AboCommerceSidebarShareMail"}{/s}"|escape}"></a>
                            {/block}
                        {/if}
                    </div>
                {/block}
            </div>
        {/if}
    {/block}
{/block}
