<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
set_exception_handler(function ($e) {
    header('HTTP/1.0 404 Not Found', true, 404);
    echo $e->getMessage();
    exit;
});

set_error_handler(function ($code, $message, $file, $line) {
    throw new Exception(sprintf('%s in file "%s" on line %d', $message, $file, $line));
}, E_ALL & ~(E_STRICT | E_NOTICE));

if (!isset($_GET['img'], $_GET['a']) || !is_array($_GET['img'])) {
    throw new InvalidArgumentException('Missing arguments');
}

Shop::Media();
