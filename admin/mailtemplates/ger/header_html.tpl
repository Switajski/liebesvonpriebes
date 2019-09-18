<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title></title>
    </head>
    <body topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#ffffff" style="margin: 0px; background-color: #ffffff;">
        <style type="text/css">
            html, body                    { background: #ffffff; }
            a, font                        { transition: all 0.5s ease; }
            #header a:hover font        { color: #8a8a8a !important; }
            #content a,
            #content font                { color: #313131; }
            #content a:hover,
            #footer a:hover font        { color: #e07568 !important; }
            table.marking a,
            table.marking font            { color: #8a8a8a; }
            .mobile-only                { max-height: 0px; font-size: 0; display: none; }

            @media (max-width: 615px) {
                table.main                { width: 320px !important; }
                table.sub                { width: 290px !important; }
                td.column                { display: block !important; float: left !important; width: 100% !important; }
                td.mobile-left            { text-align: left !important; }
                #header a font            { display: block !important; }
                #footer a font            { display: block !important; }
                span.logo                { display: block !important; text-align: center !important; }
                span.logo img            { display: inline !important; }
                .mobile-only            { max-height: none !important; font-size: 15px !important; display: inline !important; }
            }
        </style>
        <center>
            <table class="main" cellpadding="0" cellspacing="0" border="0" width="100%">
                <!-- CONTENT -->
                <tr id="content">
                    <td bgcolor="#ffffff">
                        <table cellpadding="15" cellspacing="0" border="0" width="100%">
                            <tr>
                                <td align="center" valign="top">
                                    <table class="sub" cellpadding="0" cellspacing="0" border="0" width="570">
                                        <tr>
                                            <td align="left" valign="top">
                                                {if isset($ShopLogoURL)}
                                                    <span class="logo">
                                                        <img src="{$ShopLogoURL}" alt="{$Firma->cName|escape:'quotes'}" style="display: block;">
                                                    </span><br>
                                                {/if}
                                                <br>
                                                <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">