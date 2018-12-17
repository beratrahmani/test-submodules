{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="frontend/_base/index.tpl"}

{* B2b Account Main Content *}
{block name="frontend_index_content_b2b"}

    <h1>{s name="AccessDenied"}Access denied{/s}</h1>

    <div class="alert is--error is--rounded">
        <div class="alert--icon" title="{s name="Error"}Error{/s}">
            <i class="icon--element icon--warning"></i>
        </div>
        <div class="alert--content">
            {s name="AccessDeniedMessage"}You have no access to this action.{/s}
        </div>
    </div>

{/block}