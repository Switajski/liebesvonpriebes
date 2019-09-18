<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int $kAdminlogin
 * @return mixed
 */
function getAdmin($kAdminlogin)
{
    return Shop::DB()->select('tadminlogin', 'kAdminlogin', (int)$kAdminlogin);
}

/**
 * @return mixed
 */
function getAdminList()
{
    return Shop::DB()->query(
        "SELECT * FROM tadminlogin
            LEFT JOIN tadminlogingruppe
                ON tadminlogin.kAdminlogingruppe = tadminlogingruppe.kAdminlogingruppe
         ORDER BY kAdminlogin", 2
    );
}

/**
 * @return array
 */
function getAdminGroups()
{
    $oGroups_arr = Shop::DB()->query("SELECT * FROM tadminlogingruppe", 2);
    foreach ($oGroups_arr as &$oGroup) {
        $oCount         = Shop::DB()->query("
            SELECT COUNT(*) AS nCount
              FROM tadminlogin
              WHERE kAdminlogingruppe = " . (int)$oGroup->kAdminlogingruppe, 1
        );
        $oGroup->nCount = $oCount->nCount;
    }

    return $oGroups_arr;
}

/**
 * @return array
 */
function getAdminDefPermissions()
{
    $oGroups_arr = Shop::DB()->selectAll('tadminrechtemodul', [], [], '*', 'nSort ASC');
    foreach ($oGroups_arr as &$oGroup) {
        $oGroup->oPermission_arr = Shop::DB()->selectAll(
            'tadminrecht',
            'kAdminrechtemodul',
            (int)$oGroup->kAdminrechtemodul
        );
    }

    return $oGroups_arr;
}

/**
 * @param int $kAdminlogingruppe
 * @return mixed
 */
function getAdminGroup($kAdminlogingruppe)
{
    return Shop::DB()->select('tadminlogingruppe', 'kAdminlogingruppe', (int)$kAdminlogingruppe);
}

/**
 * @param int $kAdminlogingruppe
 * @return array
 */
function getAdminGroupPermissions($kAdminlogingruppe)
{
    $oPerm_arr       = [];
    $oPermission_arr = Shop::DB()->selectAll('tadminrechtegruppe', 'kAdminlogingruppe', (int)$kAdminlogingruppe);

    foreach ($oPermission_arr as $oPermission) {
        $oPerm_arr[] = $oPermission->cRecht;
    }

    return $oPerm_arr;
}

/**
 * @param string     $cRow
 * @param string|int $cValue
 * @return bool
 */
function getInfoInUse($cRow, $cValue)
{
    $oAdmin = Shop::DB()->select('tadminlogin', $cRow, $cValue, null, null, null, null, false, $cRow);

    return is_object($oAdmin);
}

/**
 * @param int $kAdminlogin
 * @return array
 */
function benutzerverwaltungGetAttributes($kAdminlogin)
{
    $extAttribs = Shop::DB()->selectAll(
        'tadminloginattribut',
        'kAdminlogin',
        (int)$kAdminlogin,
        'kAttribut, cName, cAttribValue, cAttribText',
        'cName ASC'
    );
    if (version_compare(PHP_VERSION, '7.0', '<')) {
        $result = [];
        foreach ($extAttribs as $attrib) {
            $result[$attrib->cName] = $attrib;
        }
    } else {
        $result = array_column($extAttribs, null, 'cName');
    }

    return $result;
}

/**
 * @param stdClass $oAccount
 * @param array $extAttribs
 * @param array $messages
 * @param array $errorMap
 * @return bool
 */
function benutzerverwaltungSaveAttributes(stdClass $oAccount, array $extAttribs, array &$messages, array &$errorMap)
{
    if (isset($extAttribs) && is_array($extAttribs)) {
        $result = true;
        executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
            'oAccount' => $oAccount,
            'type'     => 'VALIDATE',
            'attribs'  => &$extAttribs,
            'messages' => &$messages,
            'result'   => &$result,
        ]);

        if ($result !== true) {
            $errorMap = array_merge($errorMap, $result);

            return false;
        }

        $handledKeys = [];
        foreach ($extAttribs as $key => $value) {
            $key      = StringHandler::filterXSS($key);
            $longText = null;

            if (is_array($value) && count($value) > 0) {
                $shortText = StringHandler::filterXSS($value[0]);
                if (count($value) > 1) {
                    $longText  = $value[1];
                }
            } else {
                $shortText = StringHandler::filterXSS($value);
            }

            $key       = Shop::DB()->escape($key);
            $shortText = Shop::DB()->escape($shortText);
            $longText  = Shop::DB()->escape($longText);

            if (!Shop::DB()->query(
                "INSERT INTO tadminloginattribut (kAdminlogin, cName, cAttribValue, cAttribText)
                    VALUES (" . (int)$oAccount->kAdminlogin . ", '" . $key . "', '" . $shortText . "', " .
                        (isset($longText)
                            ? "'" . $longText . "'"
                            : 'NULL') . ")
                    ON DUPLICATE KEY UPDATE
                    cAttribValue = '" . $shortText . "',
                    cAttribText = " . (isset($longText) ? "'" . $longText . "'" : 'NULL'), 4
            )) {
                $messages['error'] .= $key . ' konnte nicht ge&auml;ndert werden!';
            }
            $handledKeys[] = $key;
        }
        // nicht (mehr) vorhandene Attribute löschen
        Shop::DB()->query(
            "DELETE FROM tadminloginattribut
                WHERE kAdminlogin = " . (int)$oAccount->kAdminlogin . "
                    AND cName NOT IN ('" . implode("', '", $handledKeys) . "')", 4
        );
    }

    return true;
}

/**
 * @param stdClass $oAccount
 * @return boolean
 */
function benutzerverwaltungDeleteAttributes(stdClass $oAccount)
{
    return Shop::DB()->delete('tadminloginattribut', 'kAdminlogin', (int)$oAccount->kAdminlogin) >= 0;
}

/**
 * @param JTLSmarty $smarty
 * @param array $messages
 * @return string
 */
function benutzerverwaltungActionAccountLock(JTLSmarty $smarty, array &$messages)
{
    $kAdminlogin = (int)$_POST['id'];
    $oAccount    = Shop::DB()->select('tadminlogin', 'kAdminlogin', $kAdminlogin);

    if (!empty($oAccount->kAdminlogin) && $oAccount->kAdminlogin == $_SESSION['AdminAccount']->kAdminlogin) {
        $messages['error'] .= 'Sie k&ouml;nnen sich nicht selbst sperren.';
    } elseif (is_object($oAccount)) {
        if ((int)$oAccount->kAdminlogingruppe === ADMINGROUP) {
            $messages['error'] .= 'Administratoren k&ouml;nnen nicht gesperrt werden.';
        } else {
            $result = true;
            Shop::DB()->update('tadminlogin', 'kAdminlogin', $kAdminlogin, (object)['bAktiv' => 0]);
            executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
                'oAccount' => $oAccount,
                'type'     => 'LOCK',
                'attribs'  => null,
                'messages' => &$messages,
                'result'   => &$result,
            ]);
            if (true === $result) {
                $messages['notice'] .= 'Benutzer wurde erfolgreich gesperrt.';
            }
        }
    } else {
        $messages['error'] .= 'Benutzer wurde nicht gefunden.';
    }

    return 'index_redirect';
}

/**
 * @param JTLSmarty $smarty
 * @param array $messages
 * @return string
 */
function benutzerverwaltungActionAccountUnLock(JTLSmarty $smarty, array &$messages)
{
    $kAdminlogin = (int)$_POST['id'];
    $oAccount    = Shop::DB()->select('tadminlogin', 'kAdminlogin', $kAdminlogin);

    if (is_object($oAccount)) {
        $result = true;
        Shop::DB()->update('tadminlogin', 'kAdminlogin', $kAdminlogin, (object)['bAktiv' => 1]);
        executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
            'oAccount' => $oAccount,
            'type'     => 'UNLOCK',
            'attribs'  => null,
            'messages' => &$messages,
            'result'   => &$result,
        ]);
        if (true === $result) {
            $messages['notice'] .= 'Benutzer wurde erfolgreich entsperrt.';
        }
    } else {
        $messages['error'] .= 'Benutzer wurde nicht gefunden.';
    }

    return 'index_redirect';
}

/**
 * @param JTLSmarty $smarty
 * @param array $messages
 * @return string
 */
function benutzerverwaltungActionAccountEdit(JTLSmarty $smarty, array &$messages)
{
    $_SESSION['AdminAccount']->TwoFA_valid = true;

    $kAdminlogin = (isset($_POST['id']) ? (int)$_POST['id'] : null);
    // find out, if 2FA ist active and if there is a secret
    $szQRcodeString = '';
    $szKnownSecret  = '';
    if (null !== $kAdminlogin) {
        $oTwoFA = new TwoFA();
        $oTwoFA->setUserByID($_POST['id']);

        if (true === $oTwoFA->is2FAauthSecretExist()) {
            $szQRcodeString = $oTwoFA->getQRcode();
            $szKnownSecret  = $oTwoFA->getSecret();
        }
    }
    // transfer via smarty-var (to prevent session-pollution)
    $smarty->assign('QRcodeString', $szQRcodeString);
    // not nice to "show" the secret, but needed to prevent empty creations
    $smarty->assign('cKnownSecret', $szKnownSecret);

    if (isset($_POST['save'])) {
        $cError_arr           = [];
        $oTmpAcc              = new stdClass();
        $oTmpAcc->kAdminlogin = isset($_POST['kAdminlogin'])
            ? (int)$_POST['kAdminlogin']
            : 0;
        $oTmpAcc->cName       = htmlspecialchars(trim($_POST['cName']), ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $oTmpAcc->cMail       = htmlspecialchars(trim($_POST['cMail']), ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $oTmpAcc->cLogin      = trim($_POST['cLogin']);
        $oTmpAcc->cPass       = trim($_POST['cPass']);
        $oTmpAcc->b2FAauth    = (int)$_POST['b2FAauth'];
        $tmpAttribs           = isset($_POST['extAttribs']) ? $_POST['extAttribs'] : [];
        (0 < strlen($_POST['c2FAsecret'])) ? $oTmpAcc->c2FAauthSecret = trim($_POST['c2FAsecret']) : null;

        $dGueltigBisAktiv = (isset($_POST['dGueltigBisAktiv']) && ($_POST['dGueltigBisAktiv'] === '1'));
        if ($dGueltigBisAktiv) {
            try {
                $oTmpAcc->dGueltigBis = new DateTime($_POST['dGueltigBis']);
            } catch (Exception $e) {
                $oTmpAcc->dGueltigBis = '';
            }
            if ($oTmpAcc->dGueltigBis !== false && $oTmpAcc->dGueltigBis !== '') {
                $oTmpAcc->dGueltigBis = $oTmpAcc->dGueltigBis->format('Y-m-d H:i:s');
            }
        }
        $oTmpAcc->kAdminlogingruppe = (int)$_POST['kAdminlogingruppe'];

        if ((bool)$oTmpAcc->b2FAauth && !isset($oTmpAcc->c2FAauthSecret)) {
            $cError_arr['c2FAsecret'] = 1;
        }
        if (strlen($oTmpAcc->cName) === 0) {
            $cError_arr['cName'] = 1;
        }
        if (strlen($oTmpAcc->cMail) === 0) {
            $cError_arr['cMail'] = 1;
        }
        if (strlen($oTmpAcc->cPass) === 0 && $oTmpAcc->kAdminlogin === 0) {
            $cError_arr['cPass'] = 1;
        }
        if (strlen($oTmpAcc->cLogin) === 0) {
            $cError_arr['cLogin'] = 1;
        } elseif ($oTmpAcc->kAdminlogin === 0 && getInfoInUse('cLogin', $oTmpAcc->cLogin)) {
            $cError_arr['cLogin'] = 2;
        }
        if ($dGueltigBisAktiv && $oTmpAcc->kAdminlogingruppe !== ADMINGROUP) {
            if (strlen($oTmpAcc->dGueltigBis) === 0) {
                $cError_arr['dGueltigBis'] = 1;
            }
        }
        if ($oTmpAcc->kAdminlogin > 0) {
            $oOldAcc = getAdmin($oTmpAcc->kAdminlogin);
            $oCount  = Shop::DB()->query("
                SELECT COUNT(*) AS nCount
                    FROM tadminlogin
                    WHERE kAdminlogingruppe = 1", 1
            );
            if ((int)$oOldAcc->kAdminlogingruppe === ADMINGROUP &&
                (int)$oTmpAcc->kAdminlogingruppe !== ADMINGROUP &&
                $oCount->nCount <= 1) {
                $cError_arr['bMinAdmin'] = 1;
            }
        }
        if (count($cError_arr) > 0) {
            $smarty->assign('cError_arr', $cError_arr);
            $messages['error'] .= 'Bitte alle Pflichtfelder ausf&uuml;llen.';
            if (isset($cError_arr['bMinAdmin']) && (int)$cError_arr['bMinAdmin'] === 1) {
                $messages['error'] .= 'Es muss mindestens ein Administrator im System vorhanden sein.';
            }
        } else {
            if ($oTmpAcc->kAdminlogin > 0) {
                if (!$dGueltigBisAktiv) {
                    $oTmpAcc->dGueltigBis = '_DBNULL_';
                }
                // if we change the current admin-user, we have to update his session-credentials too!
                if ((int)$oTmpAcc->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin
                    && $oTmpAcc->cLogin !== $_SESSION['AdminAccount']->cLogin) {
                    $_SESSION['AdminAccount']->cLogin = $oTmpAcc->cLogin;
                }
                if (strlen($oTmpAcc->cPass) > 0) {
                    $oTmpAcc->cPass = AdminAccount::generatePasswordHash($oTmpAcc->cPass);
                    // if we change the current admin-user, we have to update his session-credentials too!
                    if ((int)$oTmpAcc->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin) {
                        $_SESSION['AdminAccount']->cPass = $oTmpAcc->cPass;
                    }
                } else {
                    unset($oTmpAcc->cPass);
                }

                if (Shop::DB()->update('tadminlogin', 'kAdminlogin', $oTmpAcc->kAdminlogin, $oTmpAcc) >= 0
                    && benutzerverwaltungSaveAttributes($oTmpAcc, $tmpAttribs, $messages, $cError_arr)
                ) {
                    $result = true;
                    executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
                        'oAccount' => $oTmpAcc,
                        'type'     => 'SAVE',
                        'attribs'  => &$tmpAttribs,
                        'messages' => &$messages,
                        'result'   => &$result,
                    ]);
                    if (true === $result) {
                        $messages['notice'] .= 'Benutzer wurde erfolgreich gespeichert.';

                        return 'index_redirect';
                    } else {
                        $smarty->assign('cError_arr', array_merge($cError_arr, (array)$result));
                    }
                } else {
                    $messages['error'] .= 'Benutzer konnte nicht gespeichert werden.';
                    $smarty->assign('cError_arr', $cError_arr);
                }
            } else {
                unset($oTmpAcc->kAdminlogin);
                $oTmpAcc->bAktiv        = 1;
                $oTmpAcc->nLoginVersuch = 0;
                $oTmpAcc->dLetzterLogin = '_DBNULL_';
                if (!isset($oTmpAcc->dGueltigBis) || strlen($oTmpAcc->dGueltigBis) === 0) {
                    $oTmpAcc->dGueltigBis = '_DBNULL_';
                }
                $oTmpAcc->cPass = AdminAccount::generatePasswordHash($oTmpAcc->cPass);

                if (($oTmpAcc->kAdminlogin = Shop::DB()->insert('tadminlogin', $oTmpAcc))
                    && benutzerverwaltungSaveAttributes($oTmpAcc, $tmpAttribs, $messages, $cError_arr)
                ) {
                    $result = true;
                    executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
                        'oAccount' => $oTmpAcc,
                        'type'     => 'SAVE',
                        'attribs'  => &$tmpAttribs,
                        'messages' => &$messages,
                        'result'   => &$result,
                    ]);
                    if (true === $result) {
                        $messages['notice'] .= 'Benutzer wurde erfolgreich hinzugef&uuml;gt';

                        return 'index_redirect';
                    } else {
                        $smarty->assign('cError_arr', array_merge($cError_arr, (array)$result));
                    }
                } else {
                    $messages['error'] .= 'Benutzer konnte nicht angelegt werden.';
                    $smarty->assign('cError_arr', $cError_arr);
                }
            }
        }

        $oAccount   = &$oTmpAcc;
        $extAttribs = [];
        foreach ($tmpAttribs as $key => $attrib) {
            $extAttribs[$key] = (object)[
                'kAttribut'    => null,
                'cName'        => $key,
                'cAttribValue' => $attrib,
            ];
        }
        if ((int)$oAccount->kAdminlogingruppe === 1) {
            unset($oAccount->kAdminlogingruppe);
        }
    } elseif ($kAdminlogin > 0) {
        $oAccount   = getAdmin($kAdminlogin);
        $extAttribs = benutzerverwaltungGetAttributes($kAdminlogin);
    } else {
        $oAccount   = new stdClass();
        $extAttribs = [];
    }

    $extContent = '';
    executeHook(HOOK_BACKEND_ACCOUNT_PREPARE_EDIT, [
        'oAccount' => $oAccount,
        'smarty'   => $smarty,
        'attribs'  => $extAttribs,
        'content'  => &$extContent,
    ]);

    $oCount = Shop::DB()->query("
        SELECT COUNT(*) AS nCount
            FROM tadminlogin
            WHERE kAdminlogingruppe = 1", 1
    );
    $smarty->assign('oAccount', $oAccount)
           ->assign('nAdminCount', $oCount->nCount)
           ->assign('extContent', $extContent);

    return 'account_edit';
}

/**
 * @param JTLSmarty $smarty
 * @param array $messages
 * @return string
 */
function benutzerverwaltungActionAccountDelete(JTLSmarty $smarty, array &$messages)
{
    $kAdminlogin = (int)$_POST['id'];
    $oCount      = Shop::DB()->query("
        SELECT COUNT(*) AS nCount
            FROM tadminlogin
            WHERE kAdminlogingruppe = 1", 1
    );
    $oAccount    = Shop::DB()->select('tadminlogin', 'kAdminlogin', $kAdminlogin);

    if (isset($oAccount->kAdminlogin) && (int)$oAccount->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin) {
        $messages['error'] .= 'Sie k&ouml;nnen sich nicht selbst l&ouml;schen';
    } elseif (is_object($oAccount)) {
        if ((int)$oAccount->kAdminlogingruppe === ADMINGROUP && $oCount->nCount <= 1) {
            $messages['error'] .= 'Es muss mindestens ein Administrator im System vorhanden sein.';
        } elseif (benutzerverwaltungDeleteAttributes($oAccount) &&
            Shop::DB()->delete('tadminlogin', 'kAdminlogin', $kAdminlogin)) {
            $result = true;
            executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
                'oAccount' => $oAccount,
                'type'     => 'DELETE',
                'attribs'  => null,
                'messages' => &$messages,
                'result'   => &$result,
            ]);
            if (true === $result) {
                $messages['notice'] .= 'Benutzer wurde erfolgreich gel&ouml;scht.';
            }
        } else {
            $messages['error'] .= 'Benutzer konnte nicht gel&ouml;scht werden.';
        }
    } else {
        $messages['error'] .= 'Benutzer wurde nicht gefunden.';
    }

    return 'index_redirect';
}

/**
 * @param JTLSmarty $smarty
 * @param array $messages
 * @return string
 */
function benutzerverwaltungActionGroupEdit(JTLSmarty $smarty, array &$messages)
{
    $bDebug            = isset($_POST['debug']);
    $kAdminlogingruppe = isset($_POST['id'])
        ? (int)$_POST['id']
        : null;
    if (isset($_POST['save'])) {
        $cError_arr                     = [];
        $oAdminGroup                    = new stdClass();
        $oAdminGroup->kAdminlogingruppe = isset($_POST['kAdminlogingruppe'])
            ? (int)$_POST['kAdminlogingruppe']
            : 0;
        $oAdminGroup->cGruppe           = htmlspecialchars(trim($_POST['cGruppe']), ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $oAdminGroup->cBeschreibung     = htmlspecialchars(trim($_POST['cBeschreibung']), ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $oAdminGroupPermission_arr      = $_POST['perm'];

        if (strlen($oAdminGroup->cGruppe) === 0) {
            $cError_arr['cGruppe'] = 1;
        }
        if (strlen($oAdminGroup->cBeschreibung) === 0) {
            $cError_arr['cBeschreibung'] = 1;
        }
        if (count($oAdminGroupPermission_arr) === 0) {
            $cError_arr['cPerm'] = 1;
        }
        if (count($cError_arr) > 0) {
            $smarty->assign('cError_arr', $cError_arr)
                   ->assign('oAdminGroup', $oAdminGroup)
                   ->assign('cAdminGroupPermission_arr', $oAdminGroupPermission_arr);

            if (isset($cError_arr['cPerm'])) {
                $messages['error'] .= 'Mindestens eine Berechtigung ausw&auml;hlen.';
            } else {
                $messages['error'] .= 'Bitte alle Pflichtfelder ausf&uuml;llen.';
            }
        } else {
            if ($oAdminGroup->kAdminlogingruppe > 0) {
                // update sql
                Shop::DB()->update(
                    'tadminlogingruppe',
                    'kAdminlogingruppe',
                    (int)$oAdminGroup->kAdminlogingruppe,
                    $oAdminGroup
                );
                // remove old perms
                Shop::DB()->delete(
                    'tadminrechtegruppe',
                    'kAdminlogingruppe',
                    (int)$oAdminGroup->kAdminlogingruppe
                );
                // insert new perms
                $oPerm                    = new stdClass();
                $oPerm->kAdminlogingruppe = (int)$oAdminGroup->kAdminlogingruppe;
                foreach ($oAdminGroupPermission_arr as $oAdminGroupPermission) {
                    $oPerm->cRecht = $oAdminGroupPermission;
                    Shop::DB()->insert('tadminrechtegruppe', $oPerm);
                }
                $messages['notice'] .= 'Gruppe wurde erfolgreich bearbeitet.';

                return 'group_redirect';
            } else {
                // insert sql
                unset($oAdminGroup->kAdminlogingruppe);
                $kAdminlogingruppe = Shop::DB()->insert('tadminlogingruppe', $oAdminGroup);
                // remove old perms
                Shop::DB()->delete('tadminrechtegruppe', 'kAdminlogingruppe', $kAdminlogingruppe);
                // insert new perms
                $oPerm                    = new stdClass();
                $oPerm->kAdminlogingruppe = $kAdminlogingruppe;
                foreach ($oAdminGroupPermission_arr as $oAdminGroupPermission) {
                    $oPerm->cRecht = $oAdminGroupPermission;
                    Shop::DB()->insert('tadminrechtegruppe', $oPerm);
                }
                $messages['notice'] .= 'Gruppe wurde erfolgreich angelegt.';

                return 'group_redirect';
            }
        }
    } elseif ($kAdminlogingruppe > 0) {
        if ((int)$kAdminlogingruppe === 1) {
            header('location: benutzerverwaltung.php?action=group_view&token=' . $_SESSION['jtl_token']);
        }
        $smarty->assign('bDebug', $bDebug)
               ->assign('oAdminGroup', getAdminGroup($kAdminlogingruppe))
               ->assign('cAdminGroupPermission_arr', getAdminGroupPermissions($kAdminlogingruppe));
    }

    return 'group_edit';
}

/**
 * @param JTLSmarty $smarty
 * @param array $messages
 * @return string
 */
function benutzerverwaltungActionGroupDelete(JTLSmarty $smarty, array &$messages)
{
    $kAdminlogingruppe = (int)$_POST['id'];

    $oResult = Shop::DB()->query("
                    SELECT count(*) AS member_count
                      FROM tadminlogin
                      WHERE kAdminlogingruppe = " . $kAdminlogingruppe, 1
    );
    // stop the deletion with a message, if there are accounts in this group
    if (0 !== (int)$oResult->member_count) {
        $messages['error'] .= 'Die Gruppe kann nicht entfernt werden, da sich noch '
                            . (2 > $oResult->member_count ? 'ein' : $oResult->member_count)
                            . ' Mitglied' . (2 > $oResult->member_count ? '' : 'er' )
                            . ' in dieser Gruppe befind' . (2 > $oResult->member_count ? 'et' : 'en') . '.<br>'
                            . 'Bitte entfernen Sie dies' . (2 > $oResult->member_count ? 'es' : 'e')
                            . ' Gruppenmitglied'  . (2 > $oResult->member_count ? '' : 'er')
                            . ' oder weisen Sie ' . (2 > $oResult->member_count ? 'es' : 'sie')
                            . ' einer anderen Gruppe zu, bevor Sie die Gruppe l&ouml;schen!';

        return 'group_redirect';
    }

    if ($kAdminlogingruppe !== ADMINGROUP) {
        Shop::DB()->delete('tadminlogingruppe', 'kAdminlogingruppe', $kAdminlogingruppe);
        Shop::DB()->delete('tadminrechtegruppe', 'kAdminlogingruppe', $kAdminlogingruppe);
        $messages['notice'] .= 'Gruppe wurde erfolgreich gel&ouml;scht.';
    } else {
        $messages['error'] .= 'Gruppe kann nicht entfernt werden.';
    }

    return 'group_redirect';
}

/**
 * @param string $cTab
 * @param array|null $messages
 */
function benutzerverwaltungRedirect($cTab = '', array &$messages = null)
{
    if (isset($messages['notice']) && !empty($messages['notice'])) {
        $_SESSION['benutzerverwaltung.notice'] = $messages['notice'];
    } else {
        unset($_SESSION['benutzerverwaltung.notice']);
    }
    if (isset($messages['error']) && !empty($messages['error'])) {
        $_SESSION['benutzerverwaltung.error'] = $messages['error'];
    } else {
        unset($_SESSION['benutzerverwaltung.error']);
    }

    $urlParams = null;
    if (!empty($cTab)) {
        $urlParams['tab'] = StringHandler::filterXSS($cTab);
    }

    header('Location: benutzerverwaltung.php' . (is_array($urlParams)
            ? '?' . http_build_query($urlParams, '', '&')
            : ''));
    exit;
}

/**
 * @param string $step
 * @param JTLSmarty $smarty
 * @param array $messages
 */
function benutzerverwaltungFinalize($step, JTLSmarty $smarty, array &$messages)
{
    if (isset($_SESSION['benutzerverwaltung.notice'])) {
        $messages['notice'] = $_SESSION['benutzerverwaltung.notice'];
        unset($_SESSION['benutzerverwaltung.notice']);
    }
    if (isset($_SESSION['benutzerverwaltung.error'])) {
        $messages['error'] = $_SESSION['benutzerverwaltung.error'];
        unset($_SESSION['benutzerverwaltung.error']);
    }

    switch ($step) {
        case 'account_edit':
            $smarty->assign('oAdminGroup_arr', getAdminGroups());
            break;
        case 'account_view':
            $smarty->assign('oAdminList_arr', getAdminList())
                   ->assign('oAdminGroup_arr', getAdminGroups());
            break;
        case 'group_edit':
            $smarty->assign('oAdminDefPermission_arr', getAdminDefPermissions());
            break;
        case 'index_redirect':
            benutzerverwaltungRedirect('account_view', $messages);
            break;
        case 'group_redirect':
            benutzerverwaltungRedirect('group_view', $messages);
            break;
    }

    $smarty->assign('hinweis', $messages['notice'])
           ->assign('fehler', $messages['error'])
           ->assign('action', $step)
           ->assign('cTab', StringHandler::filterXSS(verifyGPDataString('tab')))
           ->display('benutzer.tpl');
}

function getRandomPasswordIO()
{
    $response = new IOResponse();
    $response->assign('cPass', 'value', gibUID(8));

    return $response;
}
