{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="frontend/_base/index.tpl"}

{* Reset sidebar categories *}
{block name="frontend_index_content_left"}{/block}

{* B2b Account Main Content *}
{block name="frontend_index_content"}
    <div class="b2b--plugin content--wrapper">
        {foreach $errors as $error}
            <div class="modal--errors error--list">
                {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
            </div>
        {/foreach}

        {block name="frontend_index_content_b2b"}
            <div class="b2b--ajax-panel" data-id="salesrepresentative-grid" data-url="{url action=grid}" data-plugins="b2bGridComponent"></div>
        {/block}
    </div>
{/block}