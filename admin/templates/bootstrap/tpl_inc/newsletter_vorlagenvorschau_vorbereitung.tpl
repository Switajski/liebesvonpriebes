{include file='tpl_inc/seite_header.tpl' cTitel=#newsletterdraftStdPicture# cBeschreibung=#newsletterdesc#}
<div id="content" class="container-fluid">
    <form method="post" action="newsletter.php">
        {$jtl_token}
        <input name="tab" type="hidden" value="newslettervorlagen" />
        <table class="table newsletter">
            <tr>
                <td><b>{#newsletterdraftsubject#}</b>:</td>
                <td>{$oNewsletterVorlage->cBetreff}</td>
            </tr>

            <tr>
                <td style="vertical-align: middle;"><b>{#newsletterdraftdate#}</b>:</td>
                <td>{$oNewsletterVorlage->Datum}</td>
            </tr>
        </table>

        <h3>{#newsletterHtml#}:</h3>
        <div style="text-align: center;">
            <iframe src="{$cURL}" width="100%" height="500"></iframe>
        </div>
        <br />

        <h3>{#newsletterText#}:</h3>
        <div style="text-align: center;">
            <textarea class="form-control" style="width: 100%; height: 300px;" readonly>{$oNewsletterVorlage->cInhaltText}</textarea></div>
        <br />
        <button class="btn btn-primary" name="back" type="submit" value="{#newsletterback#}"><i class="fa fa-angle-double-left"></i> {#newsletterback#}</button>
    </form>
</div>
