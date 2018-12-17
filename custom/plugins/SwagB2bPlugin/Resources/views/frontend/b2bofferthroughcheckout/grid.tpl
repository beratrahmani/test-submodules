{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="b2b--checkout-offer">
    
    {include file="frontend/b2bofferlineitemreference/_head.tpl"}

    <div class="action--product-add">

        <button
                data-target="offer-detail-product"
                data-href="{url action="new" offerId=$offer->id}"
                class="btn component-action-create ajax-panel-link is--primary {b2b_acl controller=b2bofferthroughcheckout action=new}"
        >
            {s name="AddLineItemToOffer"}Add line item{/s}
        </button>
    </div>

    {include file="frontend/b2bofferlineitemreference/_grid.tpl"}

    <div class="block-group group-actions">
        <div class="block block-discount">

            <form action="{url action="backToCheckout"}" method="post" class="ignore--b2b-ajax-panel form--offercheckout-back">
                <input type="hidden" name="offerId" value="{$offer->id}">
                <button class="btn is--default is--large is--icon-left">{s name="BackToCart"}Back to the Cart{/s}<i class="icon--arrow-left"></i></button>
            </form>

        </div>
        <div class="block block-submit">

            <div class="offer-accept--offer">
                <form action="{url action=sendOffer}" method="post" class="{b2b_acl controller=b2bofferthroughcheckout action=sendOffer} ignore--b2b-ajax-panel">
                    <input type="hidden" name="offerId" value="{$offer->id}">
                    <input type="text" name="comment" placeholder="{s name='Comment'}Comment{/s}">
                    <button class="btn is--primary component-action-create"{if !$offer->discountAmountNet} disabled="disabled" title="{s name="SubmitOfferRequestEmpty"}The offer doesn't contain any items and cannot been processed.{/s}"{/if}>
                        {s name="SubmitOfferRequest"}Submit offer request{/s}</button>
                </form>
            </div>
        </div>
    </div>
</div>