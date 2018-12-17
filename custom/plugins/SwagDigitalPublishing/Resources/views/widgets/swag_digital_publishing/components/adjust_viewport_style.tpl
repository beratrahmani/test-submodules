{block name="widgets_digital_publishing_components_text_element_viewport_style"}
    {block name="widgets_digital_publishing_components_text_element_dynamic_style_variables"}
        {$fontsize_xs = {$element.fontsize_xs}}
        {$fontsize_s = {$element.fontsize_s}}
        {$fontsize_m = {$element.fontsize_m}}
        {$fontsize_l = {$element.fontsize_l}}
        {$fontsize_xl = {$element.fontsize_xl}}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_dynamic_viewport_variables"}
        {$viewport_s = 480}
        {$viewport_m = 768}
        {$viewport_l = 1024}
        {$viewport_xl = 1260}
        {$viewport_max = 1440}

        {if $emotion.fullscreen}
            {$viewport_max = 2048}
        {/if}
    {/block}

    {block name="widgets_digital_publishing_components_text_element_dynamic_style_adjust_dynamix"}
        {if $element.adjustDyn}
            {block name="widgets_digital_publishing_components_text_element_dynamic_style_tag"}
                <style type="text/css">
                    {block name="widgets_digital_publishing_components_text_element_dynamic_style"}
                        .digital-publishing-fluid-text-{$element.id}{ldelim}
                            font-size: {$fontsize_xs}px;
                        {rdelim}
                        @media screen and (min-width: {$viewport_s}px){ldelim}
                            .digital-publishing-fluid-text-{$element.id}{ldelim}
                                {$m = ({$fontsize_s} - {$fontsize_xs}) / ({$viewport_m} - {$viewport_s})}
                                {$b = {$fontsize_xs} - ($m * {$viewport_s})}
                                {$fontSize = "{$m} * 100vw + {$b}px"}
                                font-size: calc({$fontSize});
                            {rdelim}{rdelim}
                        @media screen and (min-width: {$viewport_m}px){ldelim}
                            .digital-publishing-fluid-text-{$element.id}{ldelim}
                                {$m = ({$fontsize_m} - {$fontsize_s}) / ({$viewport_l} - {$viewport_m})}
                                {$b = {$fontsize_s} - ($m * {$viewport_m})}
                                {$fontSize = "{$m} * 100vw + {$b}px"}
                                font-size: calc({$fontSize});
                            {rdelim}{rdelim}
                        @media screen and (min-width: {$viewport_l}px){ldelim}
                            .digital-publishing-fluid-text-{$element.id}{ldelim}
                                {$m = ({$fontsize_l} - {$fontsize_m}) / ({$viewport_xl} - {$viewport_l})}
                                {$b = {$fontsize_m} - ($m * {$viewport_l})}
                                {$fontSize = "{$m} * 100vw + {$b}px"}
                                font-size: calc({$fontSize});
                            {rdelim}{rdelim}
                        @media screen and (min-width: {$viewport_xl}px){ldelim}
                            .digital-publishing-fluid-text-{$element.id}{ldelim}
                                {$m = ({$fontsize_xl} - {$fontsize_l}) / ({$viewport_max} - {$viewport_xl})}
                                {$b = {$fontsize_l} - ($m * {$viewport_xl})}
                                {$fontSize = "{$m} * 100vw + {$b}px"}
                                font-size: calc({$fontSize});
                            {rdelim}{rdelim}
                    {/block}
                </style>
            {/block}
        {else}
            {block name="widgets_digital_publishing_components_text_element_style_tag"}
                <style type="text/css">
                    {block name="widgets_digital_publishing_components_text_element_style"}
                        .digital-publishing-fluid-text-{$element.id}{ldelim}
                            font-size:{$fontsize_xs}px;
                        {rdelim}
                        @media screen and (min-width: {$viewport_s}px){ldelim}
                            .digital-publishing-fluid-text-{$element.id}{ldelim}
                                font-size:{$fontsize_s}px;
                            {rdelim}{rdelim}
                        @media screen and (min-width: {$viewport_m}px){ldelim}
                            .digital-publishing-fluid-text-{$element.id}{ldelim}
                                font-size:{$fontsize_m}px;
                            {rdelim}{rdelim}
                        @media screen and (min-width: {$viewport_l}px){ldelim}
                            .digital-publishing-fluid-text-{$element.id}{ldelim}
                                font-size:{$fontsize_l}px;
                            {rdelim}{rdelim}
                        @media screen and (min-width: {$viewport_xl}px){ldelim}
                            .digital-publishing-fluid-text-{$element.id}{ldelim}
                                font-size:{$fontsize_xl}px;
                            {rdelim}{rdelim}
                    {/block}
                </style>
            {/block}
        {/if}
    {/block}
{/block}