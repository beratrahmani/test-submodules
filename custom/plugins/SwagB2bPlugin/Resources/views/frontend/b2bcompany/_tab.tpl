{*{namespace name=frontend/plugins/b2b_debtor_plugin}*}
{if {b2b_acl_check controller=$controller action=$action}}
    <form action="{url controller=$controller action=$action}"
          method="get"
          class="ajax-panel-link form--inline is--auto-enable-form"
          data-plugins="b2bGridComponent"
          data-target="company-tab-panel">

        <input class="b2b--tree-selection-aware"
               data-id="role-tree"
               type="hidden"
               name="roleId"
               value="">

        <button title="{$defaultTranslation|snippet:$snippetKey:"frontend/plugins/b2b_debtor_plugin"}"
                class="tab--link {b2b_acl controller=$controller action=$action}"
                type="submit"
                disabled>
            {$defaultTranslation|snippet:$snippetKey:"frontend/plugins/b2b_debtor_plugin"}
        </button>
    </form>
{/if}