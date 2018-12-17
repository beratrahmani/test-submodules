{namespace name=frontend/plugins/b2b_debtor_plugin}

{foreach $errors as $error}
    <div class="modal--errors error--list">
        {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
    </div>
{/foreach}

{if isset($success)}
    <div class="modal--errors error--list">
        {include file="frontend/_includes/messages.tpl" type="success" content="{s name="AddressSaved"}The address has been saved succesfully.{/s}"}
    </div>
{/if}

<div class="block-group b2b--form">
    <div class="block box--label">
        {s name="Type"}Addresstype{/s}: *
    </div>
    <div class="block box--input">
        <div class="select-field">
            <select name="type">
                <option value="" disabled selected="selected">{s name="Type"}Addresstype{/s}</option>
                <option value="billing" {if $type == 'billing'}selected="selected"{/if}>{s name="BillingAddress"}Billing address{/s}</option>
                <option value="shipping" {if $type == 'shipping'}selected="selected"{/if}>{s name="ShippingAddress"}Shipping address{/s}</option>
            </select>
        </div>
    </div>
</div>
<div class="block-group b2b--form">
    <div class="block box--label">
        {s name="Company"}Company{/s}: *
    </div>
    <div class="block box--input">
        <input type="text" name="company" value="{$address->company}" placeholder="{s name="Company"}Company{/s}">
    </div>
    <div class="block box--label">
        {s name="Department"}Department{/s}:
    </div>
    <div class="block box--input">
        <input type="text" name="department" value="{$address->department}" placeholder="{s name="Department"}Department{/s}">
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label">
        {s name="Salutation"}Salutation{/s}: *
    </div>
    <div class="block box--input">
        <div class="select-field">
            <select name="salutation">
                <option value="" disabled selected="selected">{s name="Salutation"}Salutation{/s} *</option>
                <option value="mr"{if $address->salutation == 'mr'} selected="selected"{/if}>{s name="Mr"}Mr{/s}</option>
                <option value="mrs"{if $address->salutation == 'mrs'} selected="selected"{/if}>{s name="Mrs"}Mrs{/s}</option>
            </select>
        </div>
    </div>
    <div class="block box--label">
        {s name="TaxId"}Tax ID{/s}
    </div>
    <div class="block box--input">
        <input type="text" name="ustid" value="{$address->ustid}" placeholder="{s name="TaxId"}Tax ID{/s}">
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label">
        {s name="FirstName"}Firstname{/s}: *
    </div>
    <div class="block box--input">
        <input type="text" name="firstname" value="{$address->firstname}" placeholder="{s name="FirstName"}Firstname{/s}">
    </div>
    <div class="block box--label">
        {s name="LastName"}Lastname{/s}: *
    </div>
    <div class="block box--input">
        <input type="text" name="lastname" value="{$address->lastname}" placeholder="{s name="LastName"}Lastname{/s}">
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label">
        {s name="Street"}Street{/s}: *
    </div>
    <div class="block box--input">
        <input type="text" name="street" value="{$address->street}" placeholder="{s name="Street"}Street{/s}">
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label">
        {s name="AddressAdditional"}Address additional{/s} 1: {if $requiredFields.addressAdditional1}*{/if}
    </div>
    <div class="block box--input">
        <input type="text" name="additional_address_line1" value="{$address->additional_address_line1}" placeholder="{s name="AddressAdditional"}Address additional{/s} 1">
    </div>
    <div class="block box--label">
        {s name="AddressAdditional"}Address additional{/s} 2: {if $requiredFields.addressAdditional2}*{/if}
    </div>
    <div class="block box--input">
        <input type="text" name="additional_address_line2" value="{$address->additional_address_line2}" placeholder="{s name="AddressAdditional"}Address additional{/s} 2">
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label">
        {s name="Postcode"}Postcode{/s}: *
    </div>
    <div class="block box--input">
        <input type="text" name="zipcode" value="{$address->zipcode}" placeholder="{s name="Postcode"}Postcode{/s}">
    </div>
    <div class="block box--label">
        {s name="City"}City{/s}: *
    </div>
    <div class="block box--input">
        <input type="text" name="city" value="{$address->city}" placeholder="{s name="City"}City{/s}">
    </div>
</div>


<div class="block-group b2b--form">

    <div class="block box--label">
        {s name="Country"}Country{/s}: *
    </div>
    <div class="block box--input">
        <div class="select-field">
            <select name="country_id">
                <option disabled selected="selected">{s name="Country"}Country{/s}  *</option>
                {foreach $countryList as $countryKey => $countryName}
                    <option value="{$countryKey}"{if $address->country_id == $countryKey} selected="selected"{/if}>{$countryName}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="block box--label">
        {s name="Phone"}Phone{/s}: {if $requiredFields.phone}*{/if}
    </div>
    <div class="block box--input">
        <input type="text" name="phone" value="{$address->phone}" placeholder="{s name="Phone"}Phone{/s}">
    </div>
</div>