{namespace name=frontend/plugins/b2b_debtor_plugin}

<table class="is--full">
    <tr>
        <td class="is--bold">{s name="Company"}Company{/s}</td>
        <td>{$address->company}</td>
    </tr>
    <tr>
        <td class="is--bold">{s name="Street"}Street{/s}</td>
        <td>{$address->street}</td>
    </tr>
    {if $address->additional_address_line1}
    <tr>
        <td class="is--bold">{s name="AddressAdditional"}Address additional{/s} 1</td>
        <td>{$address->additional_address_line1}</td>
    </tr>
    {/if}
    {if $address->additional_address_line2}
    <tr>
        <td class="is--bold">{s name="AddressAdditional"}Address additional{/s} 2</td>
        <td>{$address->additional_address_line2}</td>
    </tr>
    {/if}
    <tr>
        <td class="is--bold">{s name="City"}City{/s}</td>
        <td>{$address->city}</td>
    </tr>
    <tr>
        <td class="is--bold">{s name="Postcode"}Postcode{/s}</td>
        <td>{$address->zipcode}</td>
    </tr>
    <tr>
        <td class="is--bold">{s name="Department"}Department{/s}</td>
        <td>{$address->department}</td>
    </tr>
</table>
