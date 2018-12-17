{extends file="parent:frontend/index/index.tpl"}

{block name="frontend_index_header_title"}
    {$advisor['name']}
{/block}

{block name="frontend_index_content"}
    {include file='frontend/advisor/advisor.tpl'}
{/block}

{block name="frontend_index_header_canonical"}
    <link rel="canonical" href="{$advisorCanonicalUrl}" />
{/block}