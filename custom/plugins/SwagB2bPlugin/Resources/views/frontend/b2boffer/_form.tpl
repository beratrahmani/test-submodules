{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="block-group group--offer-details">
    <div class="block block--details">

        <div class="panel has--border offer-form">
            <div class="panel--title">
                {s name="OfferDetails"}Details{/s}
            </div>
            <div class="panel--body">
                <table>
                    <tr>
                        <td class="is--bold block-label">
                            {s name="Status"}Status{/s}:
                        </td>
                        <td class="block-value">
                            {$offer->status|snippet:$offer->status:"frontend/plugins/b2b_debtor_plugin"}
                        </td>
                    </tr>
                    <tr>
                        <td class="is--bold" class="block-label">
                            {s name="Positions"}Positions{/s}:
                        </td>
                        <td class="block-value">
                            {$itemCount}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>
    <div class="block block--prices">

        <div class="panel has--border">
            <div class="panel--title">
                {s name="Prices"}Prices{/s}
            </div>
            <div class="panel--body">
                <table>
                    <tr>
                        <td class="is--bold block-label">
                            {s name="ListPrice"}List Price{/s}:
                            <br>
                            <small>{s name="ListPrice"}List Price{/s} {s name="withTax"}with Tax{/s}:</small>
                        </td>
                        <td class="block-value">
                            {$list->amountNet|currency}
                            <br>
                            <small>{$list->amount|currency}</small>
                        </td>
                    </tr>
                    <tr>
                        <td class="is--bold block-label">
                            {s name="DiscountPrice"}Discount Price{/s}:
                            <br>
                            <small>{s name="DiscountPrice"}Discount Price{/s} {s name="withTax"}with Tax{/s}:</small>
                        </td>
                        <td class="block-value">
                            {$offer->discountAmountNet|currency}
                            <br>
                            <small>{$offer->discountAmount|currency}</small>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>
</div>

<div class="panel has--border">
    <div class="panel--title">
        {s name="Activity"}Activity{/s}
    </div>
    <div class="panel--body has--padding">
        <table>
            <tr>
                <td class="is--bold block-label">
                    {s name="Creation"}Create{/s}:
                </td>
                <td class="block-value">
                    {$offer->createdAt|date:'DATE_LONG'|default:'-'}
                </td>
            </tr>
            <tr>
                <td class="is--bold block-label">
                    {s name="ChangedStatusAt"}Changed status{/s}:
                </td>
                <td class="block-value">
                    {$offer->changedStatusAt|date:'DATE_LONG'|default:'-'}
                </td>
            </tr>
            <tr>
                <td class="is--bold block-label">
                    {s name="Expired"}Expired{/s}:
                </td>
                <td class="block-value">
                    {$offer->expiredAt|date:'DATE_LONG'|default:'-'}
                </td>
            </tr>
            <tr>
                <td class="is--bold block-label">
                    {s name="ChangedByUserAt"}Changed by User{/s}:
                </td>
                <td class="block-value">
                    {$offer->changedByUserAt|date:'DATE_LONG'|default:'-'}
                </td>
            </tr>
            <tr>
                <td class="is--bold block-label">
                    {s name="ChangedByAdminAt"}Changed by Admin{/s}:
                </td>
                <td class="block-value">
                    {$offer->changedByAdminAt|date:'DATE_LONG'|default:'-'}
                </td>
            </tr>
            <tr>
                <td class="is--bold block-label">
                    {s name="AcceptedByUser"}Accepted by User{/s}:
                </td>
                <td class="block-value">
                    {$offer->acceptedByUserAt|date:'DATE_LONG'|default:'-'}
                </td>
            </tr>
            <tr>
                <td class="is--bold block-label">
                    {s name="AcceptedByAdmin"}Accepted by Admin{/s}:
                </td>
                <td class="block-value">
                    {$offer->acceptedByAdminAt|date:'DATE_LONG'|default:'-'}
                </td>
            </tr>
            <tr>
                <td class="is--bold block-label">
                    {s name="DeclinedByUserAt"}Declined by User{/s}:
                </td>
                <td class="block-value">
                    {$offer->declinedByUserAt|date:'DATE_LONG'|default:'-'}
                </td>
            </tr>
            <tr>
                <td class="is--bold block-label">
                    {s name="DeclinedByAdminAt"}Declined by Admin{/s}:
                </td>
                <td class="block-value">
                    {$offer->declinedByAdminAt|date:'DATE_LONG'|default:'-'}
                </td>
            </tr>
        </table>
    </div>
</div>