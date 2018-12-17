{foreach $errors as $error}
    {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error assign="error"}
    {append var="jsonErrors" value=$error}
{/foreach}
{json_encode(["errors" => $jsonErrors])}
