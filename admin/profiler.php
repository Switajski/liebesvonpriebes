<?php

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Jtllog.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statistik_inc.php';

$oAccount->permission('PROFILER_VIEW', true, true);
/** @global JTLSmarty $smarty */
$tab      = 'uebersicht';
$cFehler  = '';
$cHinweis = '';
$sqlData  = null;
if (isset($_POST['delete-run-submit']) && validateToken()) {
    if (isset($_POST['run-id']) && is_numeric($_POST['run-id'])) {
        $res = deleteProfileRun(false, (int)$_POST['run-id']);
        if (is_numeric($res) && $res > 0) {
            $cHinweis = 'Eintrag erfolgreich gel&ouml;scht.';
        } else {
            $cFehler = 'Eintrag konnte nicht gel&ouml;scht werden.';
        }
    } elseif (isset($_POST['delete-all']) && $_POST['delete-all'] === 'y') {
        $res = deleteProfileRun(true);
        if (is_numeric($res) && $res > 0) {
            $cHinweis = 'Eintr&auml;ge erfolgreich gel&ouml;scht. ';
        } else {
            $cFehler = 'Eintr&auml;ge konnten nicht gel&ouml;scht werden. ';
        }
    }
}

$pluginProfilerData = Profiler::getPluginProfiles();
if (count($pluginProfilerData) > 0) {
    $axis    = new stdClass();
    $axis->x = 'filename';
    $axis->y = 'runtime';
    $idx     = 0;
    $colors  = [
        '#7cb5ec',
        '#434348',
        '#90ed7d',
        '#f7a35c',
        '#8085e9',
        '#f15c80',
        '#e4d354',
        '#8085e8',
        '#8d4653',
        '#91e8e1'
    ];
    foreach ($pluginProfilerData as $_run) {
        $hooks      = [];
        $categories = [];
        $data       = [];
        $runtime    = 0.0;
        foreach ($_run->data as $_hookExecution) {
            if (isset($_hookExecution->hookID)) {
                if (!isset($hooks[$_hookExecution->hookID])) {
                    $hooks[$_hookExecution->hookID] = [];
                }
                $hooks[$_hookExecution->hookID][] = $_hookExecution;
            }
        }
        foreach (array_keys($hooks) as $_nHook) {
            $categories[] = 'Hook ' . $_nHook;
        }
        foreach ($hooks as $hookID => $_hook) {
            $hookData                        = new stdClass();
            $hookData->y                     = 0.0;
            $hookData->color                 = $colors[$idx];
            $hookData->drilldown             = new stdClass();
            $hookData->drilldown->name       = 'Hook ' . $hookID;
            $hookData->drilldown->categories = [];
            $hookData->drilldown->data       = [];
            $hookData->drilldown->runcount   = [];
            $hookData->color                 = $colors[$idx];
            foreach ($_hook as $_file) {
                $hookData->y += ((float)$_file->runtime * 1000);
                $runtime += $hookData->y;
                $hookData->drilldown->categories[] = $_file->filename;
                $hookData->drilldown->data[]       = ((float)$_file->runtime * 1000);
                $hookData->drilldown->runcount[]   = $_file->runcount;
            }
            $data[] = $hookData;
            if (++$idx >= count($colors)) {
                $idx = 0;
            }
        }
        $_run->pieChart             = new stdClass();
        $_run->pieChart->categories = json_encode($categories);
        $_run->pieChart->data       = json_encode($data);
        $_run->runtime              = $runtime;
    }
}

$sqlProfilerData = Profiler::getSQLProfiles();
$smarty->assign('pluginProfilerData', $pluginProfilerData)
       ->assign('sqlProfilerData', $sqlProfilerData)
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('tab', $tab)
       ->display('profiler.tpl');

/**
 * @param bool $all
 * @param int  $runID
 * @return mixed
 */
function deleteProfileRun($all = false, $runID = 0)
{
    if ($all === true) {
        $count = Shop::DB()->query("DELETE FROM tprofiler", 3);
        Shop::DB()->query("ALTER TABLE tprofiler AUTO_INCREMENT = 1", 3);
        Shop::DB()->query("ALTER TABLE tprofiler_runs AUTO_INCREMENT = 1", 3);

        return $count;
    }

    return Shop::DB()->delete('tprofiler', 'runID', $runID);
}
