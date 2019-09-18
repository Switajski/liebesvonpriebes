<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/../../../includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

$einstellungApiKey       = Shop::DB()->query("
    SELECT cWert 
        FROM teinstellungen 
        WHERE cName = 'zahlungsart_safetypay_apikey'", 1
);
$einstellungSignatureKey = Shop::DB()->query("
    SELECT cWert 
        FROM teinstellungen 
        WHERE cName = 'zahlungsart_safetypay_signaturekey'", 1
);

define('SAFETYPAY_APIKEY', $einstellungApiKey->cWert);
define('SAFETYPAY_SIGNTATURE_KEY', $einstellungSignatureKey->cWert);

require_once __DIR__ . '/class/safetypayProxyAPI.php';

// Create instance of Class
$proxySTP = new SafetyPayProxy();
// 1.- Get New Paid Orders in Banks
$Result = $proxySTP->GetNewPaidOrders();

if ($Result['ErrorManager']['ErrorNumber'] == '0') {
    $txtGetNewPaidOrders  = '';
    $confirmnewpaidorders = ['Items' => []];
    $nCounter             = 0;
    if (is_array($Result['ListOfNewPaidOrders']['Items'])) {
        if (isset($Result['ListOfNewPaidOrders']['Items']['ReferenceNo'])) {
            $oResult = $Result['ListOfNewPaidOrders'];
        } else {
            $oResult = $Result['ListOfNewPaidOrders']['Items'];
        }

        foreach ($oResult as $key => $value) {
            $MerchantOrderNo = $value['MerchantReferenceNo'];

            // IMPORTANT!
            // YOUR CODE HERE
            // You will be receive order paids in the variable $oResult, you can use this
            // to change your orders from pending to complete.
            // In this variable are include your MerchantReferenceNo ( sames as used in the CreateTransaction() call )

            // If you create and OrderNo different than the MerchantReferenceNo
            // $MerchantOrderNo = 'YOUR ORDER NUMBER';
            // else $MerchantOrderNo = $value['MerchantReferenceNo'];

            $zahlungsid = Shop::DB()->select('tbestellung', 'cBestellNr', $MerchantOrderNo);
            $b          = Shop::DB()->query("
                SELECT kKunde 
                    FROM tbestellung 
                    WHERE kBestellung = " . (int)$zahlungsid->kBestellung, 1
            );
            $kunde      = Shop::DB()->select('tkunde', 'kKunde', (int)$b->kKunde);
            $Sprache    = Shop::DB()->query("
                SELECT cISO 
                    FROM tsprache 
                    WHERE kSprache = " . (int)$kunde->kSprache, 1
            );
            if (!$Sprache) {
                $Sprache = Shop::DB()->query("SELECT cISO FROM tsprache WHERE cStandard = 'Y'", 1);
            }

            $bestellung = new Bestellung($zahlungsid->kBestellung);
            $bestellung->fuelleBestellung(0);

            //zahlung setzen
            $_upd                = new stdClass();
            $_upd->cStatus       = BESTELLUNG_STATUS_BEZAHLT;
            $_upd->dBezahltDatum = 'now()';
            Shop::DB()->update('tbestellung', 'kBestellung', (int)$bestellung->kBestellung, $_upd);

            $bestellung = new Bestellung($zahlungsid->kBestellung);
            $bestellung->fuelleBestellung(0);

            // process payment
            $paymentDateTmp                     = strtotime($value['PaymentDate']);
            $zahlungseingang                    = new stdClass();
            $zahlungseingang->kBestellung       = $bestellung->kBestellung;
            $zahlungseingang->cZahlungsanbieter = 'SafetyPay';
            $zahlungseingang->fBetrag           = $bestellung->fGesamtsummeKundenwaehrung;
            $zahlungseingang->cISO              = $bestellung->Waehrung->cISO;
            $zahlungseingang->cZahler           = $kunde->cMail;
            $zahlungseingang->cAbgeholt         = 'N';
            $zahlungseingang->dZeit             = strftime('%Y-%m-%d %H:%M:%S', $paymentDateTmp);
            Shop::DB()->insert('tzahlungseingang', $zahlungseingang);

            $obj->tkunde->cMail = $kunde->cMail;
            //mail
            $obj->tkunde      = $kunde;
            $obj->tbestellung = $bestellung;

            sendeMail(MAILTEMPLATE_BESTELLUNG_BEZAHLT, $obj);

            $confirmnewpaidorders['Items'][] = [
                'ReferenceNo'     => $value['ReferenceNo'],
                'PaymentDate'     => $value['PaymentDate'],
                'MerchantOrderNo' => $MerchantOrderNo,
                'IssueCode'       => $value['IssueCode']
            ];
        }
        $nCounter = count($confirmnewpaidorders['Items']);
        // 6. Confirm the "SAFETYPAY Merchant Order Number" and execute function ConfirmNewPaidOrders
        $Result = $proxySTP->ConfirmNewPaidOrders($confirmnewpaidorders);
        if ($Result['ErrorManager']['ErrorNumber'] == '') {
            $txtGetNewPaidOrders = 'Confirmed Transactions Reference No: ' . "\n" . $strTransConfirmeds;
        } else {
            $txtGetNewPaidOrders = 'Error: ' . $Result['ErrorManager']['ErrorNumber'] .
                ' - ' . $Result['ErrorManager']['Description'];
        }
    } else {
        $txtGetNewPaidOrders .= 'No New Paid Orders';
    }

    if ($nCounter === 0) {
        echo 'No registrations processed';
    } else {
        echo "<center><b>" . (string) $nCounter . " verarbeitete Datens&auml;tze";
        echo "</br></br>";
        echo "Vielen Dank. Der Vorgang wurde erfolgreich durchgef&uuml;hrt!</b>";
        echo "</center>";
    }
} else {
    // Error in Conection to SAFETYPAY Web Service
    echo 'Error in GetNewPaidOrders Method: Invalid Credentials!<br />';
    echo 'Error Number: ' . $Result['ErrorManager']['ErrorNumber'] . '<br />Severity: ' .
        $Result['ErrorManager']['Severity'] . '<br />Description: ' . $Result['ErrorManager']['Description'];
}
