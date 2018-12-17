<!DOCTYPE HTML>
<html>
<head>
    <title>Warenkorb</title>
    <style type="text/css">
        td {
            font-family: Arial, Helvetica;
            color: #4D4D4D;
        }

        a:link, a:visited {
            color: blue;
            text-decoration: none;
        }

        a:hover, a:active {
            color: #000;
            text-decoration: none;
        }

        a:hover {
            color: #000;
            text-decoration: none;
        }
    </style>
</head>

<body style="height:100%; font-family:Arial, Helvetica, sans-serif; padding:0; background-color:#E9EBED;" background="#ffffff;margin:0;padding:0;" leftmargin="0" topmargin="0">

<table align="center" width="100%" border="0" cellspacing="25" cellpadding="0" style="color:#8c8c8c;font-family:Arial,Helvetica;">
    <tr>
        <td>
            <table align="center" width="560" bgcolor="#ffffff" border="0" cellspacing="25" cellpadding="0" style="color:#4D4D4D; border:1px solid #dfdfdf;font-family:Arial,Helvetica;">
                <tr>
                    <td>
                        Guten Tag!<br><br>
                        {$name} möchte seine Wunschliste im Shop "{$shopName}" mit Ihnen teilen.<br>
                        Mit nur einem Klick können Sie sich die Wunschliste anschauen.
                        <br>
                        <br>
                        <hr>
                        <p>Persönliche Nachricht:</p>
                        {$message}
                        <hr>
                        <br>
                        <br>
                        Klicken Sie <a href="{$url}">hier</a>, um die Wunschliste anzuzeigen.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>