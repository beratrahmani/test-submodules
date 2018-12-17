{if $b2bSuite && {b2b_acl_check controller=b2borderlistremote action=remoteList}}
    <div class="is--b2b-ajax-panel b2b--ajax-panel b2b--ajax-panel-orderlist"
         data-id="order-list-remote-box"
         data-plugins="b2bOrderList"
         data-url="{url controller=b2borderlistremote action=remoteList referenceNumber=$orderNumber b2b_quantity=$quantity type=detail}"></div>
{/if}