<?php
/**
 * Created by ag-websolutions.de
 *
 * File: agws_ts_features_hook_75.php
 * Project: agws_trustedshops
 */

$_SESSION['agws_kWarenkorb_TS'] = $args_arr['oBestellung']->kWarenkorb;
$_SESSION['agws_kKunde_TS'] = $args_arr['oBestellung']->kKunde;