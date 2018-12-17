{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal.tpl"}

{block name="b2b_modal_base" prepend}
    {$modalSettings.navigation = false}
{/block}

{block name="b2b_modal_base_content_inner"}
    {extends file="parent:frontend/b2bofferlineitemreference/new.tpl"}

    {block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
        <form action="{url action=create}"
              method="post"
              data-ajax-panel-trigger-reload="offer-grid"
              id="form"
              data-close-success="true"
              class="form--inline {b2b_acl controller=b2bofferthroughcheckout action=create}"
        >
            <input type="hidden" name="offerId" value="{$offer->id}">
            {include file="frontend/b2bofferlineitemreference/_form.tpl"}
        </form>
    {/block}
{/block}