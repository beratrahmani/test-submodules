{block name="widgets_digital_publishing_components_text_element"}

    {block name="widgets_digital_publishing_components_text_element_padding"}
        {$padding = "padding: {$element.paddingTop / 16}rem {$element.paddingRight / 16}rem {$element.paddingBottom / 16}rem {$element.paddingLeft / 16}rem;"}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_style_font_family"}
        {$style = "font-family: {$element.font};"}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_style_line_height"}
        {$style = "{$style} line-height: {$element.lineHeight};"}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_style_color"}
        {$style = "{$style} color: {$element.fontcolor};"}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_style_text_align"}
        {$style = "{$style} text-align: {$element.orientation};"}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_white_space_fix_ie"}
        {*Internet explorer 11 fix for long texts*}
        {$style = "{$style} white-space: pre-wrap;"}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_style_font_weight"}
        {if $element.fontweight}
            {$style = "{$style} font-weight: bold;"}
        {/if}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_style_font_style"}
        {if $element.fontstyle}
            {$style = "{$style} font-style: italic;"}
        {/if}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_style_text_decoration_underline"}
        {if $element.underline}
            {$style = "{$style} text-decoration: underline;"}
        {/if}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_style_text_decoration_uppercase"}
        {if $element.uppercase}
            {$style = "{$style} text-transform: uppercase;"}
        {/if}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_style_text_shadow"}
        {if $element.shadowColor}
            {$style = "{$style} text-shadow: {$element.shadowOffsetX}px {$element.shadowOffsetY}px {$element.shadowBlur}px {$element.shadowColor};"}
        {/if}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_default_element_type"}
        {if !$element.type}
            {$element.type = 'p'}
        {/if}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_responsive_text"}
        {if $element.adjust}
            {include file="widgets/swag_digital_publishing/components/adjust_viewport_style.tpl"}
        {else}
            {$style = "{$style} font-size: {$element.fontsize / 16}rem;"}
        {/if}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_text"}
        <div class="dig-pub--text" style="{$padding}">
            <{$element.type} style="{$style}" class="digital-publishing-fluid-text-{$element.id}{if $element.class} {$element.class}{/if}">{$element.text|nl2br}</{$element.type}>
        </div>
    {/block}
{/block}