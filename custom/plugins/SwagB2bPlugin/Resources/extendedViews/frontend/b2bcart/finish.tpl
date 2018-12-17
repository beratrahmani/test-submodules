{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="frontend/checkout/finish.tpl"}

{block name="frontend_index_content"}
    <div class="content checkout--content finish--content">

        <div class="finish--teaser panel has--border is--rounded">

            <h2 class="panel--title teaser--title is--align-center">{s name="OrderForwardedToClearance"}The order was forwarded to clearance{/s}</h2>

            <div class="panel--body is--wide is--align-center">
                <p>
                    {s name="OrderCheckoutToClearanceMessage"}The order will now be cleared by a supervisor. Thank you for your request!{/s}
                </p>
                <p>
                    <a href="{url controller="index"}"
                       class="btn is--primary is--large is--icon-left"
                       title="{s name="BackToShop"}Back to shop{/s}"
                    >
                        {s name="BackToShop"}Back to shop{/s}
                        <i class="icon--arrow-left"></i>
                    </a>
                    <a href="{url controller="b2border"}"
                       class="btn is--primary is--large is--icon-right {b2b_acl controller=b2border action=index}"
                       title="{s name="OrderOverview"}Order overview{/s}"
                    >
                        {s name="OrderOverview"}Order overview{/s}
                        <i class="icon--arrow-right"></i>
                    </a>
                </p>
            </div>
        </div>
    </div>
{/block}
