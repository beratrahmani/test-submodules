{block name="frontend_advisor_sidebar_question_info_text"}
    <div class="question-ct--additional-info" data-title="{$question['question']|escapeHtmlAttr|truncate:65}">
        {block name="frontend_advisor_sidebar_question_info_text"}
            <div class="additional-info--hidden-info is--hidden">{$question['infoText']}</div>
        {/block}
        <span>{$advisor['infoLinkText']|truncate:35} &nbsp;</span>
        <i class="icon--service"></i>
    </div>
{/block}

{* Contains the main info for a question, if any is given *}
{block name="frontend_advisor_sidebar_question_info_content"}
    <div class="question-ct--off-canvas-info">
        <div class="buttons--off-canvas">
            <a href="#" class="close--off-canvas">
                <i class="icon--arrow-left"></i> {s name="OffcanvasCloseMenu" namespace="frontend/detail/description"}{/s}
            </a>
        </div>
        {block name="frontend_advisor_sidebar_question_info_ct"}
            <div class="content--description">
                {block name="frontend_advisor_sidebar_question_info_title"}
                    <div class="content--title">
                        {$question['question']}
                    </div>
                {/block}

                    {block name="frontend_advisor_sidebar_question_info_desc"}
                    <div class="off-canvas-info--text">
                        {$question['infoText']}
                    </div>
                {/block}
            </div>
        {/block}
    </div>
{/block}
