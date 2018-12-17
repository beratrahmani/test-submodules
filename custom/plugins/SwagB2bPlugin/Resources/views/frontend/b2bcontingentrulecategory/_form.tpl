{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="block-group b2b--form">
    <div class="block box--label  is--full">
        {s name="Category"}Category{/s}: *
    </div>
    <div class="block box--input  is--full">
        <input type="hidden" name="categoryId" id="categoryId" value="{$rule->categoryId}">
        <div
                class="b2b--ajax-panel is--b2b-tree is--b2b-tree-container"
                data-plugins="b2bTree"
                data-tree-connected-input-id="categoryId"
                data-url="{url controller=b2bcategoryselect action=grid selectedId=$rule->categoryId}" data-id="contingent-rule-type-form"
        ></div>
    </div>
</div>
