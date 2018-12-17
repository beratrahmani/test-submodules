{namespace name=frontend/plugins/b2b_debtor_plugin}

{if $debtorEntity}
    <div class="salesRepresentativeBar">
        <div class="block-group">
            <div class="block block-meta">
                {s name="SalesRepresentativeBarLoggedInAs"}You are logged in as{/s}: {$debtorEntity->firstName} {$debtorEntity->lastName}
            </div>
            <div class="block block-actions">
                <div class="block-actions--link">
                    <a href="{url controller='b2bsalesrepresentative' action='salesRepresentativeLogin'}" title="{s name="ClientOverview"}Client Overview{/s}">{s name="ClientOverview"}Client Overview{/s}</a>
                </div>
            </div>
        </div>
    </div>
{/if}
