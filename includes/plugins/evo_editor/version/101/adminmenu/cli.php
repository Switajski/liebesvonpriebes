<?php

ini_set('display_errors', 1);

if (PHP_SAPI !== 'cli') {
    exit;
}

if (!defined('PFAD_ROOT')) {
    require_once __DIR__ . '/../../../../../globalinclude.php';
    if (!isset($oPlugin)) {
        $oPlugin = Plugin::getPluginById('evo_editor');
    }
}
if (empty($oPlugin)) {
    echo 'Plugin could not be loaded. Is it activated?' . PHP_EOL;
    exit;
}

require_once $oPlugin->cAdminmenuPfad . '/EvoEditor.php';
$themesToCompile = null;
if ($argc > 1) {
    $themesToCompile = [];
    for ($i = 1; $i < $argc; ++$i) {
        $themesToCompile[] = $argv[$i];
    }
}
$evoEditor = EvoEditor::getInstance();
$themes    = $evoEditor->getThemes($themesToCompile);
$count     = count($themes);
$compiled  = 0;
$cacheDir  = PFAD_ROOT . PFAD_COMPILEDIR . 'less/*';
foreach (glob($cacheDir) as $file) {
    unlink($file);
}

foreach ($themes as $_theme) {
    $start = microtime(true);

    printf("%2d / %d %-10s", ++$compiled, $count, $_theme['theme']);

    $result = $evoEditor->compile($_theme['theme'], $_theme['template']);
    $err    = $result['data']['type'] !== 'success';
    $end    = (microtime(true) - $start);
    $msg    = $result['data']['msg'];

    printf("\033[0;%dm[%.2fs]\033[0m %s\n", $err ? 31 : 32, $end, $msg);
}
