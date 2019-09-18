<?php

/*
 * Controller class that handles recurring functions.
 */
require_once('lpa_includes.php');

require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");
require_once('lpa_defines.php');

/*
// TODO: Is this still needed: Include path must be adjusted for Amazon SDK to work (all its includes are relative to this path!)?
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)));
*/
// include Amazon Pay SDK - the SDK declares the Namespace "AmazonPay" - see https://github.com/amzn/amazon-pay-sdk-php/
require_once __DIR__ . "/amazon-pay-sdk-3.2.0/AmazonPay/Client.php";
require_once __DIR__ . "/amazon-pay-sdk-3.2.0/AmazonPay/Regions.php";


class LPAController {

    /**
     * Endpoint-URLs according to integration guide:
     *
     * https://images-na.ssl-images-amazon.com/images/G/02/mwsportal/doc/en_US/offamazonpayments/LoginAndPayWithAmazonIntegrationGuide._V326922526_.pdf
     */
    static $ENDPOINTS = array(
        "MWS" => array(
            "de" => array(
                "sandbox" => "https://mws-eu.amazonservices.com/OffAmazonPayments_Sandbox/2013-01-01",
                "production" => "https://mws-eu.amazonservices.com/OffAmazonPayments/2013-01-01"
            ),
            "eu" => array(
                "sandbox" => "https://mws-eu.amazonservices.com/OffAmazonPayments_Sandbox/2013-01-01",
                "production" => "https://mws-eu.amazonservices.com/OffAmazonPayments/2013-01-01"
            ),
            "uk" => array(
                "sandbox" => "https://mws-eu.amazonservices.com/OffAmazonPayments_Sandbox/2013-01-01",
                "production" => "https://mws-eu.amazonservices.com/OffAmazonPayments/2013-01-01"
            ),
            "na" => array(
                "sandbox" => "https://mws.amazonservices.com/OffAmazonPayments_Sandbox/2013-01-01",
                "production" => "https://mws.amazonservices.com/OffAmazonPayments/2013-01-01"
            )
        ),
        "Widget" => array(
            "de" => array(
                "sandbox" => "https://static-eu.payments-amazon.com/OffAmazonPayments/de/sandbox/lpa/js/Widgets.js",
                "production" => "https://static-eu.payments-amazon.com/OffAmazonPayments/de/lpa/js/Widgets.js"
            ),
            "uk" => array(
                "sandbox" => "https://static-eu.payments-amazon.com/OffAmazonPayments/uk/sandbox/lpa/js/Widgets.js",
                "production" => "https://static-eu.payments-amazon.com/OffAmazonPayments/uk/lpa/js/Widgets.js"
            ),
            "us" => array(
                "sandbox" => "https://static-na.payments-amazon.com/OffAmazonPayments/us/sandbox/js/Widgets.js",
                "production" => "https://static-na.payments-amazon.com/OffAmazonPayments/us/js/Widgets.js"
            )
        ),
        "Login" => array(
            "de" => array(
                "sandbox" => "https://api.sandbox.amazon.de/",
                "production" => "https://api.amazon.de/"
            ),
            "uk" => array(
                "sandbox" => "https://api.sandbox.amazon.co.uk/",
                "production" => "https://api.amazon.co.uk/"
            )
        ),
        "Profile" => array(
            "de" => array(
                "sandbox" => array(
                    'https://api.sandbox.amazon.de/auth/o2/tokeninfo?access_token=',
                    'https://api.sandbox.amazon.de/user/profile'
                ),
                "production" => array(
                    'https://api.amazon.de/auth/o2/tokeninfo?access_token=',
                    'https://api.amazon.de/user/profile'
                )
            ),
            "uk" => array(
                "sandbox" => array(
                    'https://api.sandbox.amazon.co.uk/auth/o2/tokeninfo?access_token=',
                    'https://api.sandbox.amazon.co.uk/user/profile'
                ),
                "production" => array(
                    'https://api.amazon.co.uk/auth/o2/tokeninfo?access_token=',
                    'https://api.amazon.co.uk/user/profile'
                )
            ),
            "us" => array(
                "sandbox" => array(
                    'https://api.sandbox.amazon.com/auth/o2/tokeninfo?access_token=',
                    'https://api.sandbox.amazon.com/user/profile'
                ),
                "production" => array(
                    'https://api.amazon.com/auth/o2/tokeninfo?access_token=',
                    'https://api.amazon.com/user/profile'
                )
            )
        )
    );

    public function getClient($config = null) {
        if ($config == null) {
            $config = $this->getConfig();
        }
        $client = new \AmazonPay\Client($config);
        return $client;
    }

    /*
     * Loads the plugin configuration for client access.
     * 
     * $config['merchantId']: User config
     * !$config['accessKey']: User config
     * !$config['secretKey']: User config
     * !$config['applicationName']: CONSTANT for this plugin
     * !$config['applicationVersion']: CONSTANT for this plugin version
     * !$config['region']: User config
     * !$config['environment']: User config ('sandbox') or not
     * $config['serviceURL']: result of combination of region and environment OR user config
     * $config['widgetURL']: result of combination of region and environment OR user config
     * $config['caBundleFile']: ???
     * $config['clientId']: User config (shop specific)
     */

    public function getConfig() {
        return $this->_loadCustomConfigComplete();
    }

    /*
     * Similar to the getConfig-function, but this one returns a preset applicationName and applicationVersion.
     * Only used by the admin backend.
     */
    public function getInitialConfig() {
        $config = array();
        $config['merchant_id'] = $this->_loadCustomConfigValueFor(S360_LPA_CONFKEY_MERCHANT_ID);
        $config['access_key'] = $this->_loadCustomConfigValueFor(S360_LPA_CONFKEY_ACCESS_KEY);
        $config['secret_key'] = $this->_loadCustomConfigValueFor(S360_LPA_CONFKEY_SECRET_KEY);
        $config['application_name'] = S360_LPA_APPLICATION_NAME;
        $config['application_version'] = S360_LPA_APPLICATION_VERSION;
        $config['region'] = $this->_loadCustomConfigValueFor(S360_LPA_CONFKEY_REGION);
        $config['sandbox'] = ($this->_loadCustomConfigValueFor(S360_LPA_CONFKEY_ENVIRONMENT) === 'sandbox');
        $config['client_id'] = $this->_loadCustomConfigValueFor(S360_LPA_CONFKEY_CLIENT_ID);
        return $config;
    }

    /*
     * Returns the full endpoint URL, depending on the requested type and region/environment settings in the config
     */

    public function getEndpointFor($config, $type) {
        if (empty($config)) {
            $config = $this->getConfig();
        }

        $region = $config['region'];
        if ($region !== "uk" && $region !== "us") {
            $region = "de";
        }

        $environment = "production";
        if ($config['sandbox'] === true) {
            $environment = "sandbox";
        }

        if ($type === 'serviceURL') {
            return self::$ENDPOINTS['MWS'][$region][$environment];
        } else {
            return self::$ENDPOINTS['Widget'][$region][$environment];
        }
    }

    /*
     * Returns the profile information API URLs depending on the region/environment settings in the config.
     *
     * The first element is the Token Info URL, the second element is the User Profile URL
     */

    public function getProfileAPIURLs($config = null) {
        if (empty($config)) {
            $config = $this->getConfig();
        }
        $region = $config['region'];
        if ($region !== "uk" && $region !== "us") {
            $region = "de";
        }
        $environment = "production";
        if ($config['sandbox'] === true) {
            $environment = "sandbox";
        }
        return self::$ENDPOINTS['Profile'][$region][$environment];
    }

    /*
     * Returns the currency code, based on the given config
     */

    public function getCurrencyCode($config) {
        $currency = 'EUR'; // default to euros
        if (!empty($config)) {
            if (empty($config['region'])) {
                return $currency;
            } elseif ($config['region'] === 'de') {
                $currency = 'EUR';
            } elseif ($config['region'] === 'uk') {
                $currency = 'GBP';
            } elseif ($config['region'] === 'us') {
                $currency = 'USD';
            }
        }
        return $currency;
    }

    private function _loadCustomConfigValueFor($configKey) {
        $result = Shop::DB()->query('SELECT * FROM ' . S360_LPA_TABLE_CONFIG . ' WHERE cName LIKE "' . $configKey . '" LIMIT 1', 1);
        if (!empty($result)) {
            return $result->cWert;
        } else {
            return '';
        }
    }

    private function _loadCustomConfigComplete() {
        $config = array();
        $result = Shop::DB()->query('SELECT cName,cWert FROM ' . S360_LPA_TABLE_CONFIG, 2);
        if (!empty($result)) {
            /*
             * turn result array into assoc array for quick access
             */
            $configAssoc = array();
            foreach ($result as $configEntry) {
                $configAssoc[$configEntry->cName] = $configEntry->cWert;
            }
            $config['merchant_id'] = $configAssoc[S360_LPA_CONFKEY_MERCHANT_ID];
            $config['access_key'] = $configAssoc[S360_LPA_CONFKEY_ACCESS_KEY];
            $config['secret_key'] = $configAssoc[S360_LPA_CONFKEY_SECRET_KEY];
            $config['application_name'] = S360_LPA_APPLICATION_NAME;
            $config['application_version'] = S360_LPA_APPLICATION_VERSION;
            $config['region'] = $configAssoc[S360_LPA_CONFKEY_REGION];
            $config['sandbox'] = ($configAssoc[S360_LPA_CONFKEY_ENVIRONMENT] === 'sandbox');
            $config['client_id'] = $configAssoc[S360_LPA_CONFKEY_CLIENT_ID];
        }
        return $config;
    }

}
