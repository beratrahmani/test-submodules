{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="companyFilterTypeContainer">
    <input type="hidden" name="roleId" value="{$companyFilter.roleId}"/>
    <select name="companyFilterType" class="is--auto-submit">
        {if $companyFilter.level}
            <option value="assignment" {if $companyFilter.companyFilterType == "assignment"}selected{/if}>{s name="FilterAssignment"}Filter assignment{/s}</option>
            {if !$hide}
                <option value="acl" {if $companyFilter.companyFilterType == "acl"}selected{/if}>{s name="FilterVisibility"}Filter visibility{/s}</option>
            {/if}
        {/if}
        <option value="inheritance" {if $companyFilter.companyFilterType == "inheritance"}selected{/if}>{s name="FilterInheritance"}Filter inheritance{/s}</option>
    </select>
</div>
