{if $b2bSuite && {b2b_acl_check controller=b2bfastorderremote action=remoteListFastOrder}}
    <div class="is--b2b-ajax-panel b2b--ajax-panel b2b--ajax-panel-orderlist"
         data-id="fast-order-remote-box"
         data-plugins="b2bOrderList"
         data-url="{url controller=b2bfastorderremote action=remoteListFastOrder}"></div>
{/if}