<?php

if (!function_exists('paypal_tlstest')) {
    function paypal_tlstest()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://tlstest.paypal.com/");
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Some environments may be capable of TLS 1.2 but it is not in their 
        // list of defaults so need the SSL version option to be set.
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_exec($ch);

        $res = curl_error($ch);

        curl_close($ch);

        return $res;
    }
}

if (PHP_SAPI == 'cli') {
    $error = paypal_tlstest();

    if (!$error) {
        echo "OK";
    } else {
        echo $error . PHP_EOL . PHP_EOL;
        echo "------------------------------" . PHP_EOL;
        echo json_encode(curl_version(), JSON_PRETTY_PRINT);
    }
    echo PHP_EOL;
}
