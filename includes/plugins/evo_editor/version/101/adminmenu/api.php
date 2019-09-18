<?php

if (!defined('PFAD_ROOT')) {
    require_once __DIR__ . '/../../../../../globalinclude.php';
    if (!isset($oPlugin)) {
        $oPlugin = Plugin::getPluginById('evo_editor');
    }
}
require_once __DIR__ . '/EvoEditor.php';

$evoEditor = EvoEditor::getInstance();
if (isset($_REQUEST['action'])) {
    $evoEditor->json($_REQUEST['action']);
} else {
    $evoEditor->showEditor();
}
