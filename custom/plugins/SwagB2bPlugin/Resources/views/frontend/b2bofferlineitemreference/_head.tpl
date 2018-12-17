{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="offerthroughcheckout--title">
    <h2 class="is--align-center title-supertitle">{s name="OfferPersonalTitle"}Your personal offer{/s}</h2>
    <p class="is--align-center title--subline">
        {s name="OfferPersonalTitleSubline"}Request your inidividual offer in few steps based on your cart.{/s}
    </p>
</div>

<div class="block-group group--offer-compare">
    <div class="block block--original">

        <div class="panel has--border panel--original">
            <h3 class="panel--title is--underline">
                {s name="OfferPriceOriginal"}Original price{/s}
            </h3>
            <div class="panel--body is--wide">

                <div class="block-group group--details">
                    <div class="block block--label is--align-right">{s name="OfferPriceTotalNet"}Total amount net{/s}:</div>
                    <div class="block block--value is--align-right">{$amountNet|currency}</div>
                </div>
                <div class="block-group group--details">
                    <div class="block block--label is--align-right">{s name="OfferPriceTax"}Taxes{/s}:</div>
                    <div class="block block--value is--align-right">{($amount - $amountNet)|currency}</div>
                </div>
                <div class="block-group group--details">
                    <div class="block block--label is--align-right">{s name="OfferPriceTotalTax"}Total amount with taxes{/s}:</div>
                    <div class="block block--value is--align-right">{$amount|currency}</div>
                </div>

            </div>
        </div>

    </div>
    <div class="block block--offer">

        <div class="panel has--border panel--offer">
            <h3 class="panel--title is--underline">
                {s name="OfferPersonalTitle"}Your perosnal offer{/s}
            </h3>
            <div class="panel--body is--wide">

                <div class="block-group group--details">
                    <div class="block block--label is--align-right">{s name="OfferTotalNet"}Requested total amount net{/s}:</div>
                    <div class="block block--value is--align-right">{$offer->discountAmountNet|currency}</div>
                </div>

                {if $offer->discountValueNet}
                    <div class="block-group group--details">
                        <div class="block block--label is--align-right">{s name="OfferDiscountTitleNet"}Discount Net{/s}:</div>
                        <div class="block block--value is--align-right">{$offer->discountValueNet|currency}</div>
                    </div>
                {/if}

                <div class="block-group group--details">
                    <div class="block block--label is--align-right">{s name="OfferTaxes"}Taxes{/s}:</div>
                    <div class="block block--value is--align-right">{($offer->discountAmount - $offer->discountAmountNet)|currency}</div>
                </div>
                <div class="block-group group--details">
                    <div class="block block--label is--align-right">{s name="OfferAmountTotal"}Desired total with taxes{/s}:</div>
                    <div class="block block--value is--align-right">{$offer->discountAmount|currency}</div>
                </div>

            </div>
            <h3 class="panel--title is--underline title--discount">
                {s name="OfferWishDiscount"}Desired discount{/s}:
            </h3>
            <div class="panel--body is--wide body--discount">

                <p>{s name="OfferDiscountDescription"}Enter your desired total discount net. Confirm your discount with the save button.{/s}</p>

                <form action="{url action=updateDiscount}" method="post">

                    <div class="offer-input-group">
                        <input type="number"
                               name="discount"
                               min="0"
                               step="any"
                               placeholder="{s name="AddDiscount"}Add discount{/s}"
                               {if $offer->discountValueNet}value="{$offer->discountValueNet}"{/if}
                                {if !$offer->isEditableByUser()} disabled="disabled"{/if}>
                        <span class="group-addon is--right is--wide">{b2b_currency_symbol}</span>
                        <button type="submit"
                                class="btn is--secondary"
                                {if !$offer->isEditableByUser()}disabled="disabled"{/if}
                        >
                            {s name="Save"}Save{/s}
                        </button>
                    </div>
                    <input type="hidden" name="offerId" value="{$offer->id}">
                </form>

            </div>
        </div>

    </div>
</div>
