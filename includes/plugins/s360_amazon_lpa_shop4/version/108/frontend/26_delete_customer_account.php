<?php
/*
 * The customer in the current session deleted his account, so we delete the account mapping as well.
 */
require_once('lib/lpa_defines.php');
Shop::DB()->query('DELETE FROM '.S360_LPA_TABLE_ACCOUNTMAPPING.' WHERE kKunde=' . intval($_SESSION['Kunde']->kKunde), 4);
