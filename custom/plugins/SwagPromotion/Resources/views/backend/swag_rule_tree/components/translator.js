
// {namespace name=backend/swag_promotion/field_translations}
// {block name="backend/swag_rule_tree/components/translator"}
Ext.define('Shopware.apps.SwagTreeRule.components.Translator', {

    snippets: undefined,

    init: function () {
        var me = this;

        me.createTranslationSnippets();
    },

    translateSnippet: function (value) {
        var me = this;

        if (me.snippets.hasOwnProperty(value)) {
            return me.snippets[value];
        }

        return value;
    },

    createTranslationSnippets: function () {
        var me = this;

        me.snippets = {
            'supplier::name': '{s name=supplierName}Supplier name{/s}',
            'product::id': '{s name=productId}Product ID{/s}',
            'product::supplierID': '{s name=productSupplierID}Supplier ID{/s}',
            'product::taxID': '{s name=productTaxId}Tax ID{/s}',
            'product::pricegroupID': '{s name=productPriceGroupId}Price group ID{/s}',
            'product::filtergroupID': '{s name=productFilterGroupId}Filter group ID{/s}',
            'product::name': '{s name=productName}Product name{/s}',
            'product::description': '{s name=productDescription}Short description{/s}',
            'product::description_long': '{s name=productDescriptionLong}Description{/s}',
            'product::active': '{s name=productActive}Product is active{/s}',
            'product::topseller': '{s name=productTopseller}Product is top seller{/s}',
            'product::keywords': '{s name=productKeywords}Product keywords{/s}',
            'product::metaTitle': '{s name=productMetaTitle}Product meta title{/s}',
            'product::pricegroupActive': '{s name=productPriceGroupActive}Product active price group{/s}',
            'productAttribute::attr1': '{s name=attr1}Attribute 1{/s}',
            'productAttribute::attr2': '{s name=attr2}Attribute 2{/s}',
            'productAttribute::attr3': '{s name=attr3}Attribute 3{/s}',
            'productAttribute::attr4': '{s name=attr4}Attribute 4{/s}',
            'productAttribute::attr5': '{s name=attr5}Attribute 5{/s}',
            'productAttribute::attr6': '{s name=attr6}Attribute 6{/s}',
            'productAttribute::attr7': '{s name=attr7}Attribute 7{/s}',
            'productAttribute::attr8': '{s name=attr8}Attribute 8{/s}',
            'productAttribute::attr9': '{s name=attr9}Attribute 9{/s}',
            'productAttribute::attr10': '{s name=attr10}Attribute 10{/s}',
            'productAttribute::attr11': '{s name=attr11}Attribute 11{/s}',
            'productAttribute::attr12': '{s name=attr12}Attribute 12{/s}',
            'productAttribute::attr13': '{s name=attr13}Attribute 13{/s}',
            'productAttribute::attr14': '{s name=attr14}Attribute 14{/s}',
            'productAttribute::attr15': '{s name=attr15}Attribute 15{/s}',
            'productAttribute::attr16': '{s name=attr16}Attribute 16{/s}',
            'productAttribute::attr17': '{s name=attr17}Attribute 17{/s}',
            'productAttribute::attr18': '{s name=attr18}Attribute 18{/s}',
            'productAttribute::attr19': '{s name=attr19}Attribute 19{/s}',
            'productAttribute::attr20': '{s name=attr20}Attribute 20{/s}',
            'detail::id': '{s name=detailsId}Variant ID{/s}',
            'detail::ordernumber': '{s name=detailsOrderNumber}Ordernumber{/s}',
            'detail::kind': '{s name=detailsKind}Variant kind{/s}',
            'detail::active': '{s name=detailsActive}Variant active{/s}',
            'detail::instock': '{s name=detailsInstock}Instock{/s}',
            'detail::laststock': '{s name=productLastStock}Clearance{/s}',
            'detail::stockmin': '{s name=detailsStockMin}Min instock{/s}',
            'detail::weight': '{s name=detailsWeight}Weight{/s}',
            'detail::width': '{s name=detailsWidth}Width{/s}',
            'detail::length': '{s name=detailsLength}Length{/s}',
            'detail::height': '{s name=detailsHeight}Height{/s}',
            'detail::ean': '{s name=detailsEan}EAN{/s}',
            'detail::purchaseunit': '{s name=detailsPurchase}Purchase unit{/s}',
            'detail::shippingfree': '{s name=shippingFree}Shipping free{/s}',
            'detail::purchaseprice': '{s name=detailsPurchasePrice}Purchasing price{/s}',
            'price::id': '{s name=priceId}Price ID{/s}',
            'price::from': '{s name=priceFrom}Price From{/s}',
            'price::to': '{s name=priceTo}Price to{/s}',
            'price::price': '{s name=price}Price{/s}',
            'price::pseudoprice': '{s name=pricePseudoprice}Pseudo price{/s}',
            'price::baseprice': '{s name=priceBasePrice}Base price{/s}',
            'price::percent': '{s name=pricePercentDiscount}Percentage discount{/s}',
            'categories.id': '{s name=categoryId}Category ID{/s}',
            'categories.description': '{s name=categoryDescription}Category description{/s}',
            'categories.meta_title': '{s name=categoryMetaTitle}Category meta title{/s}',
            'categories.metakeywords': '{s name=categoryMetaKeywords}Category meta keywords{/s}',
            'categories.metadescription': '{s name=categoryMetaDescription}Category meta description{/s}',
            'categories.cmsheadline': '{s name=categoryCmsHeadline}Category title{/s}',
            'categories.cmstext': '{s name=categoryCmsText}Category description{/s}',
            'categories.active': '{s name=categoryActive}Category is active{/s}',
            'categories.external_target': '{s name=categoryExternalTarget}{/s}',
            'amountGross': '{s name=amountGross}Total price{/s}',
            'amountNet': '{s name=amountNet}Total price net{/s}',
            'numberOfProducts': '{s name=numberOfProducts}Number of products{/s}',
            'shippingFree': '{s name=shippingFree}Shipping free{/s}',
            'user::id': '{s name=userId}Customer ID{/s}',
            'user::customergroup': '{s name=userCustomerGroup}Customer group{/s}',
            'user::paymentID': '{s name=userPaymentId}Payment ID{/s}',
            'user::language': '{s name=userLanguage}Customer shop{/s}',
            'user::email': '{s name=userEmail}Customer email{/s}',
            'user::accountmode': '{s name=userAccountmode}Customer account mode{/s}',
            'user::validation': '{s name=userValidation}Customer validation{/s}',
            'user::paymentpreset': '{s name=userPaymentpreset}Payment preset{/s}',
            'user::internalcomment': '{s name=userInternalcomment}Internal comment{/s}',
            'user::customernumber': '{s name=userCustomerNumber}Customer number{/s}',
            'user::birthday': '{s name=userBirthday}Billing address Customer birthday{/s}',
            'user::title': '{s name=userTitle}Customer Title{/s}',
            'user::firstname': '{s name=userFirstName}First name{/s}',
            'user::salutation': '{s name=userSalutation}Salutation{/s}',
            'user::lastname': '{s name=userLastName}Last name{/s}',
            'address::company': '{s name=userAddressCompany}Address company{/s}',
            'address::department': '{s name=userAddressDepartment}Address department{/s}',
            'address::salutation': '{s name=userAddressSalutation}Address salutation{/s}',
            'address::firstname': '{s name=userAddressFirstName}Address first name{/s}',
            'address::lastname': '{s name=userAddressLastName}Address last name{/s}',
            'address::street': '{s name=userAddressStreet}Address street{/s}',
            'address::zipcode': '{s name=userAddressZipCode}Address zip code{/s}',
            'address::city': '{s name=userAddressCity}Address city{/s}',
            'address::additional_address_line1': '{s name=userAddressAddOne}Address additional line one{/s}',
            'address::additional_address_line2': '{s name=userAddressAddTwo}Address additional line two{/s}',
            'address::title': '{s name=userAddressTitle}Address title{/s}',
            'address::ustid': '{s name=userAddressUstId}Address tax id{/s}',
            'address::phone': '{s name=userAddressPhone}Address phone{/s}',
            'address::country_id': '{s name=userAddressCountry}Address country{/s}',
            'address::state_id': '{s name=userAddressState}Address state{/s}',
            'customer_stream::id': '{s name=customerStream}Customer Stream{/s}'
        };
    }
});
// {/block}
