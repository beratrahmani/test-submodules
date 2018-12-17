<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="author" content=""/>
<meta name="copyright" content="" />

<title></title>
<style type="text/css">
* {
	margin: 0;
	padding: 0;
}
body {
	font-family: Helvetica, Arial;
	margin: 0;
	padding: 0;
}
#container {
	background-color: #fff;
	padding: 3px;
	width: 90%;
}
.ticketAnswers .answer {
    margin-top: 20px;
}
.ticketdetail_txtbox {
    border: 1px solid;
}
.ticketdetail_attach {
    border: 1px solid;
    border-top: none;
}
.container {
    padding: 5px 10px;
}
</style>
<body>
    <div id="container">
        <div>{$ticket.receipt|date}</div>
        <div class="ticketdetail_txtbox container">
            <div class="row">
                {s namespace="backend/ticket/pdf" name="ticketType"}Type{/s}: {$ticket.ticketTypeName}
            </div>
            <div class="row">
                {s namespace="backend/ticket/pdf" name="ticketIso"}ISO{/s}: {$ticket.isoCode}
            </div>
            <div class="row">
                {s namespace="backend/ticket/pdf" name="ticketSender"}Sender{/s}: {$ticket.email}
            </div>
            <div class="row">
                {s namespace="backend/ticket/pdf" name="ticketSubject"}Subject{/s}: {$ticket.subject}
            </div> 
            <div class="row">
                {s namespace="backend/ticket/pdf" name="ticketMessage"}Message{/s}: {$ticket.message}
            </div>
            {if $ticket.additional}
                {foreach from=$ticket.additional item=additional}
                <div class="row">
                {$additional.label}: {$additional.value}
                </div>
                {/foreach}
            {/if}
            <div class="row">
                {s namespace="backend/ticket/pdf" name="ticketStatus"}Status{/s}: {$ticket.status}
            </div>
            <div class="row">
                {s namespace="backend/ticket/pdf" name="ticketlastContact"}Last contact{/s}: {$ticket.lastContact|date}
            </div>
        </div>
        {if $ticketHistory}
        <div class="ticketAnswers">
            {foreach from=$ticketHistory item=answer}
            <div class="answer">
                <div>{$answer.receipt|date}</div>
                <div class="ticketdetail_txtbox container">{$answer.message}</div>
                {if $answer.attachment}
                <div class="ticketdetail_attach container">
                {foreach from=$answer.attachment item=attachment}
                    <div>{s namespace="backend/ticket/pdf" name="ticketAttachment"}Attachment{/s}: {$attachment}</div>
                {/foreach}
                </div>
                {/if}
            </div>
            {/foreach}
        </div>
        {/if}
    </div>
</body>
</html>