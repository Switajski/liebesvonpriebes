<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * mail functions
 *
 * @param string $FromName
 * @param string $FromMail
 * @param string $ReplyAdresse
 * @param string $To
 * @param string $Subject
 * @param string $Text
 * @param string $Html
 * @return bool
 */
function SendNiceMailReply($FromName, $FromMail, $ReplyAdresse, $To, $Subject, $Text, $Html = '')
{
    //endl definieren
    $eol = "\n";
    if (strpos(strtoupper(PHP_OS), 'WIN') === 0) {
        $eol = "\r\n";
    } elseif (strpos(strtoupper(PHP_OS), 'MAC') === 0) {
        $eol = "\r";
    }

    $FromName = StringHandler::unhtmlentities($FromName);
    $FromMail = StringHandler::unhtmlentities($FromMail);
    $Subject  = StringHandler::unhtmlentities($Subject);
    $Text     = StringHandler::unhtmlentities($Text);

    $Text = $Text ?: 'Sorry, but you need an html mailer to read this mail.';

    if (empty($To)) {
        return false;
    }

    $mime_boundary = md5(time()) . '_jtlshop2';
    $headers       = '';

    if (strpos($To, 'freenet')) {
        $headers .= 'From: ' . strtolower($FromMail) . $eol;
    } else {
        $headers .= 'From: ' . $FromName . ' <' . strtolower($FromMail) . '>' . $eol;
    }

    $headers .= 'Reply-To: ' . strtolower($ReplyAdresse) . $eol;
    $headers .= 'MIME-Version: 1.0' . $eol;
    if (!$Html) {
        $headers .= 'Content-Type: text/plain; charset=' . JTL_CHARSET . $eol;
        $headers .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
    }

    $Msg = $Text;
    if ($Html) {
        $Msg = '';
        $headers .= 'Content-Type: multipart/alternative; boundary=' . $mime_boundary . $eol;

        # Text Version
        $Msg .= '--' . $mime_boundary . $eol;
        $Msg .= 'Content-Type: text/plain; charset=' . JTL_CHARSET . $eol;
        $Msg .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
        $Msg .= $Text . $eol;

        # HTML Version
        $Msg .= '--' . $mime_boundary . $eol;
        $Msg .= 'Content-Type: text/html; charset=' . JTL_CHARSET . $eol;
        $Msg .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
        $Msg .= $Html . $eol . $eol;

        # Finished
        $Msg .= '--' . $mime_boundary . '--' . $eol . $eol;
    }
    mail($To, encode_iso88591($Subject), $Msg, $headers);

    return true;
}

/**
 * @param string $string
 * @return string
 */
function encode_iso88591($string)
{
    $text = '=?' . JTL_CHARSET . '?Q?';
    $max  = strlen($string);
    for ($i = 0; $i < $max; $i++) {
        $val = ord($string[$i]);
        if ($val > 127 || $val === 63) {
            $val = dechex($val);
            $text .= '=' . $val;
        } else {
            $text .= $string[$i];
        }
    }
    $text .= '?=';

    return $text;
}
