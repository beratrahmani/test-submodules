{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="b2b--modal">
    <div class="topbar">
        <h3 class="panel--title">{s name="SelectAddress"}Select address{/s}</h3>
    </div>
    <div class="b2b--plugin scrollable with--padding">
        {include file="frontend/_includes/messages.tpl" type="info" content={"SelectAddressInfo"|snippet:"SelectAddressInfo":"frontend/plugins/b2b_debtor_plugin"}}
    </div>
</div>