{extends file="parent:widgets/emotion/index.tpl"}

{block name="widgets/emotion/index/config"}
    {$smarty.block.parent}

    {if $emotion.mode === "storytelling"}
        {$emotionSectionRows = $emotion.attribute.swag_rows}

        {* Compatibility for 5.2.x *}
        {if !$emotionSectionRows}
            {$emotionSectionRows = $emotion.attribute.swagRows}
        {/if}

        {$emotionFullscreen = true}
    {/if}
{/block}

{block name="widgets/emotion/index/attributes"}
    {$smarty.block.parent}

    {$swagQuickView = $emotion.attribute.swag_quickview}

    {* Compatibility for 5.2.x *}
    {if !$swagQuickView}
        {$swagQuickView = $emotion.attribute.swagQuickview}
    {/if}

    {strip}
        data-quickview="{if $swagQuickView}true{else}false{/if}"
        data-ajaxUrl="{url module='Widgets' controller='SwagEmotionAdvanced' method='index'}"
    {/strip}
{/block}

{block name="widgets/emotion/index/emotion"}
    {if $emotion.mode === "storytelling"}
        <div class="emotion-storytelling"
             data-storytelling="true"
             data-rowsPerSection="{$emotionSectionRows}">
            {$smarty.block.parent}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
