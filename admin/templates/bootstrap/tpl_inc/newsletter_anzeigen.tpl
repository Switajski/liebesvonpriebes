<div id="page">
    <div id="content" class="container-fluid">
        <form method="post" action="newsletter.php">
            {$jtl_token}
            <div id="welcome" class="post">
                <h2 class="title"><span>{#newsletterhistory#}</span></h2>

                <div class="content">
                    <p>{#newsletterdesc#}</p>
                </div>
            </div>
            <table class="newsletter table">
                <tr>
                    <td><strong>{#newsletterdraftsubject#}</strong>:</td>
                    <td>{$oNewsletterHistory->cBetreff}</td>
                </tr>
                <tr>
                    <td><strong>{#newsletterdraftdate#}</strong>:</td>
                    <td>{$oNewsletterHistory->Datum}</td>
                </tr>
            </table>
            <h3>{#newsletterHtml#}:</h3>
            <p>{$oNewsletterHistory->cHTMLStatic}</p>
            <p class="submit-wrapper">
                <button class="btn btn-primary" name="back" type="submit" value="{#newsletterback#}"><i class="fa fa-angle-double-left"></i> {#newsletterback#}</button>
            </p>
        </form>
    </div>
</div>