<div class="listing--container b2b--listing-table">
    <div>
        <table>
            <tr class="listing-table--headline">
                <th>{s name="ListingTableProductNumber" namespace="frontend/plugins/b2b_debtor_plugin"}Ordernumber{/s}</th>
                <th>{s name="ListingTableProductName" namespace="frontend/plugins/b2b_debtor_plugin"}Article{/s}</th>
                <th>{s name="ListingTableProductPrice" namespace="frontend/plugins/b2b_debtor_plugin"}Price{/s}</th>
                <th>{s name="ListingTableProductQuantity" namespace="frontend/plugins/b2b_debtor_plugin"}Quantity{/s}</th>
            </tr>
            {foreach $sArticles as $sArticle}
                {include file="frontend/listing/box_article.tpl" b2bProductBoxLayout=$b2bListingView iteration=$sArticle@iteration}
            {/foreach}
        </table>
    </div>
</div>