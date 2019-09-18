<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
Profiler::savePluginProfile();
Profiler::saveSQLProfile();
Profiler::output();
if (Profiler::getIsStarted() === true) {
    Profiler::finish();
    $data = Profiler::getData();
    echo $data['html'];
}
