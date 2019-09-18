<?php
/*
 * Various constants are defined here.
 */

/*
 * Table names.
 */
defined('S360_LPA_TABLE_CONFIG') || define('S360_LPA_TABLE_CONFIG', 'xplugin_s360_amazon_lpa_shop4_tconfig');
defined('S360_LPA_TABLE_ACCOUNTMAPPING') || define('S360_LPA_TABLE_ACCOUNTMAPPING', 'xplugin_s360_amazon_lpa_shop4_taccountmapping');
defined('S360_LPA_TABLE_ORDER') || define('S360_LPA_TABLE_ORDER', 'xplugin_s360_amazon_lpa_shop4_torder');
defined('S360_LPA_TABLE_AUTHORIZATION') || define('S360_LPA_TABLE_AUTHORIZATION', 'xplugin_s360_amazon_lpa_shop4_tauthorization');
defined('S360_LPA_TABLE_CAPTURE') || define('S360_LPA_TABLE_CAPTURE', 'xplugin_s360_amazon_lpa_shop4_tcapture');
defined('S360_LPA_TABLE_REFUND') || define('S360_LPA_TABLE_REFUND', 'xplugin_s360_amazon_lpa_shop4_trefund');
defined('S360_LPA_TABLE_CRON') || define('S360_LPA_TABLE_CRON', 'xplugin_s360_amazon_lpa_shop4_tcron');

defined('S360_LPA_TABLE_JTL_CONFIG') || define('S360_LPA_TABLE_JTL_CONFIG', 'tplugineinstellungen');
/*
 * Application specific constants.
 */
defined('S360_LPA_APPLICATION_NAME') || define('S360_LPA_APPLICATION_NAME', 'Solution360 LPA Plugin for JTL-Shop 4');
defined('S360_LPA_APPLICATION_VERSION') || define('S360_LPA_APPLICATION_VERSION', '1.0.5');
defined('S360_LPA_PLATFORM_ID') || define('S360_LPA_PLATFORM_ID', 'A2EMAQC31RHCFG');

/*
 * Other technical constants
 */
defined('S360_LPA_PLUGIN_ID') || define('S360_LPA_PLUGIN_ID', 's360_amazon_lpa_shop4');
defined('S360_LPA_TEST_IPN_ID') || define('S360_LPA_TEST_IPN_ID', 'P01-0000000-0000000-000000');
// Error constant that signifies when the status handler encountered an unknown payment object ID
defined('S360_LPA_ERROR_UNKNOWN_ID') || define('S360_LPA_ERROR_UNKNOWN_ID', 'lpa_error_unknown_id');


/*
 * Config keys, custom table.
 */
defined('S360_LPA_CONFKEY_MERCHANT_ID') || define('S360_LPA_CONFKEY_MERCHANT_ID', 'merchant_id');
defined('S360_LPA_CONFKEY_ACCESS_KEY') || define('S360_LPA_CONFKEY_ACCESS_KEY', 'mws_access_key');
defined('S360_LPA_CONFKEY_SECRET_KEY') || define('S360_LPA_CONFKEY_SECRET_KEY', 'mws_secret_key');
defined('S360_LPA_CONFKEY_ENVIRONMENT') || define('S360_LPA_CONFKEY_ENVIRONMENT', 'mws_environment');
defined('S360_LPA_CONFKEY_REGION') || define('S360_LPA_CONFKEY_REGION', 'mws_region');
defined('S360_LPA_CONFKEY_CLIENT_ID') || define('S360_LPA_CONFKEY_CLIENT_ID', 'lpa_client_id');
defined('S360_LPA_CONFKEY_CLIENT_SECRET') || define('S360_LPA_CONFKEY_CLIENT_SECRET', 'lpa_client_secret');
defined('S360_LPA_CONFKEY_EXCLUDED_DELIVERY_METHODS') || define('S360_LPA_CONFKEY_EXCLUDED_DELIVERY_METHODS', 'lpa_excluded_delivery_methods');

/*
 * Config keys, in default table.
 */
defined('S360_LPA_CONFKEY_GENERAL_ACTIVE') || define('S360_LPA_CONFKEY_GENERAL_ACTIVE', 'lpa_general_active');
defined('S360_LPA_CONFKEY_GENERAL_MODE') || define('S360_LPA_CONFKEY_GENERAL_MODE', 'lpa_general_mode');
defined('S360_LPA_CONFKEY_HIDDENBUTTONS_ACTIVE') || define('S360_LPA_CONFKEY_HIDDENBUTTONS_ACTIVE', 'lpa_general_hiddenbuttons_active');
defined('S360_LPA_CONFKEY_FULL_DELIVERY_ADDRESS') || define('S360_LPA_CONFKEY_FULL_DELIVERY_ADDRESS', 'lpa_login_full_delivery_address');
defined('S360_LPA_CONFKEY_EXCLUDE_PACKSTATION') || define('S360_LPA_CONFKEY_EXCLUDE_PACKSTATION', 'lpa_login_exclude_packstation');
defined('S360_LPA_CONFKEY_LOGINBUTTON_TYPE') || define('S360_LPA_CONFKEY_LOGINBUTTON_TYPE', 'lpa_template_login_type');
defined('S360_LPA_CONFKEY_LOGINBUTTON_COLOR') || define('S360_LPA_CONFKEY_LOGINBUTTON_COLOR', 'lpa_template_login_color');
defined('S360_LPA_CONFKEY_LOGINBUTTON_SIZE') || define('S360_LPA_CONFKEY_LOGINBUTTON_SIZE', 'lpa_template_login_size');
defined('S360_LPA_CONFKEY_PAYBUTTON_TYPE') || define('S360_LPA_CONFKEY_PAYBUTTON_TYPE', 'lpa_template_pay_type');
defined('S360_LPA_CONFKEY_PAYBUTTON_COLOR') || define('S360_LPA_CONFKEY_PAYBUTTON_COLOR', 'lpa_template_pay_color');
defined('S360_LPA_CONFKEY_PAYBUTTON_SIZE') || define('S360_LPA_CONFKEY_PAYBUTTON_SIZE', 'lpa_template_pay_size');

defined('S360_LPA_CONFKEY_LOGINBUTTON_INSERT_METHOD') || define('S360_LPA_CONFKEY_LOGINBUTTON_INSERT_METHOD', 'lpa_template_login_insert_method');
defined('S360_LPA_CONFKEY_LOGINBUTTON_INSERT_SELECTORS') || define('S360_LPA_CONFKEY_LOGINBUTTON_INSERT_SELECTORS', 'lpa_template_login_insert_selectors');

defined('S360_LPA_CONFKEY_PAYBUTTON_INSERT_METHOD') || define('S360_LPA_CONFKEY_PAYBUTTON_INSERT_METHOD', 'lpa_template_pay_insert_method');
defined('S360_LPA_CONFKEY_PAYBUTTON_INSERT_SELECTORS') || define('S360_LPA_CONFKEY_PAYBUTTON_INSERT_SELECTORS', 'lpa_template_pay_insert_selectors');

defined('S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT') || define('S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT', 'lpa_template_pay_during_checkout');
defined('S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT_INSERT_METHOD') || define('S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT_INSERT_METHOD', 'lpa_template_pay_during_checkout_insert_method');
defined('S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT_INSERT_SELECTORS') || define('S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT_INSERT_SELECTORS', 'lpa_template_pay_during_checkout_insert_selectors');


defined('S360_LPA_CONFKEY_LOGINBUTTON_INSERT_METHOD_MOBILE') || define('S360_LPA_CONFKEY_LOGINBUTTON_INSERT_METHOD_MOBILE', 'lpa_template_login_insert_method_mobile');
defined('S360_LPA_CONFKEY_LOGINBUTTON_INSERT_SELECTORS_MOBILE') || define('S360_LPA_CONFKEY_LOGINBUTTON_INSERT_SELECTORS_MOBILE', 'lpa_template_login_insert_selectors_mobile');

defined('S360_LPA_CONFKEY_PAYBUTTON_INSERT_METHOD_MOBILE') || define('S360_LPA_CONFKEY_PAYBUTTON_INSERT_METHOD_MOBILE', 'lpa_template_pay_insert_method_mobile');
defined('S360_LPA_CONFKEY_PAYBUTTON_INSERT_SELECTORS_MOBILE') || define('S360_LPA_CONFKEY_PAYBUTTON_INSERT_SELECTORS_MOBILE', 'lpa_template_pay_insert_selectors_mobile');

defined('S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT_MOBILE') || define('S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT_MOBILE', 'lpa_template_pay_during_checkout_mobile');
defined('S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT_INSERT_METHOD_MOBILE') || define('S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT_INSERT_METHOD_MOBILE', 'lpa_template_pay_during_checkout_insert_method_mobile');
defined('S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT_INSERT_SELECTORS_MOBILE') || define('S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT_INSERT_SELECTORS_MOBILE', 'lpa_template_pay_during_checkout_insert_selectors_mobile');

defined('S360_LPA_CONFKEY_WIDGET_WIDTH') || define('S360_LPA_CONFKEY_WIDGET_WIDTH', 'lpa_template_widget_width');
defined('S360_LPA_CONFKEY_WIDGET_HEIGHT') || define('S360_LPA_CONFKEY_WIDGET_HEIGHT', 'lpa_template_widget_height');


defined('S360_LPA_CONFKEY_WIDGET_WIDTH_MOBILE') || define('S360_LPA_CONFKEY_WIDGET_WIDTH_MOBILE', 'lpa_template_widget_width_mobile');
defined('S360_LPA_CONFKEY_WIDGET_HEIGHT_MOBILE') || define('S360_LPA_CONFKEY_WIDGET_HEIGHT_MOBILE', 'lpa_template_widget_height_mobile');


defined('S360_LPA_CONFKEY_ADVANCED_AUTHMODE') || define('S360_LPA_CONFKEY_ADVANCED_AUTHMODE', 'lpa_advanced_authmode');
defined('S360_LPA_CONFKEY_ADVANCED_CAPTUREMODE') || define('S360_LPA_CONFKEY_ADVANCED_CAPTUREMODE', 'lpa_advanced_capturemode');
defined('S360_LPA_CONFKEY_ADVANCED_USEIPN') || define('S360_LPA_CONFKEY_ADVANCED_USEIPN', 'lpa_advanced_useipn');
defined('S360_LPA_CONFKEY_ADVANCED_POLLINTERVAL') || define('S360_LPA_CONFKEY_ADVANCED_POLLINTERVAL', 'lpa_advanced_pollinterval');
defined('S360_LPA_CONFKEY_ADVANCED_AUTHSTATE') || define('S360_LPA_CONFKEY_ADVANCED_AUTHSTATE', 'lpa_advanced_authstate');
defined('S360_LPA_CONFKEY_ADVANCED_CAPTURESTATE') || define('S360_LPA_CONFKEY_ADVANCED_CAPTURESTATE', 'lpa_advanced_capturestate');
defined('S360_LPA_CONFKEY_ADVANCED_JSPOSITION') || define('S360_LPA_CONFKEY_ADVANCED_JSPOSITION', 'lpa_advanced_js_position');
defined('S360_LPA_CONFKEY_ADVANCED_SHOP3_COMPATIBILITY') || define('S360_LPA_CONFKEY_ADVANCED_SHOP3_COMPATIBILITY', 'lpa_advanced_shop3_compatibility');
defined('S360_LPA_CONFKEY_ADVANCED_BACKOFF_BACKEND') || define('S360_LPA_CONFKEY_ADVANCED_BACKOFF_BACKEND', 'lpa_advanced_backoff_backend');

/*
 * Language variables
 */
defined('S360_LPA_LANGKEY_TOOLTIP') || define('S360_LPA_LANGKEY_TOOLTIP', 'lpa_tooltip');
defined('S360_LPA_LANGKEY_CHECKOUT_HINT') || define('S360_LPA_LANGKEY_CHECKOUT_HINT', 'lpa_checkout_hint');
defined('S360_LPA_LANGKEY_CONFIRMATION_SHIPPING_ADDRESS') || define('S360_LPA_LANGKEY_CONFIRMATION_SHIPPING_ADDRESS', 'lpa_confirmation_shipping_address');
defined('S360_LPA_LANGKEY_CONFIRMATION_PAYMENT_METHOD') || define('S360_LPA_LANGKEY_CONFIRMATION_PAYMENT_METHOD', 'lpa_confirmation_payment_method');
defined('S360_LPA_LANGKEY_CONFIRMATION_SOFT_DECLINE') || define('S360_LPA_LANGKEY_CONFIRMATION_SOFT_DECLINE', 'lpa_confirmation_soft_decline');
defined('S360_LPA_LANGKEY_CONFIRMATION_HARD_DECLINE') || define('S360_LPA_LANGKEY_CONFIRMATION_HARD_DECLINE', 'lpa_confirmation_hard_decline');
defined('S360_LPA_LANGKEY_CONFIRMATION_CHECKBOXES') || define('S360_LPA_LANGKEY_CONFIRMATION_CHECKBOXES', 'lpa_confirmation_checkboxes');
defined('S360_LPA_LANGKEY_TECHNICAL_ERROR') || define('S360_LPA_LANGKEY_TECHNICAL_ERROR', 'lpa_technical_error');
defined('S360_LPA_LANGKEY_GENERIC_ERROR') || define('S360_LPA_LANGKEY_GENERIC_ERROR', 'lpa_generic_error');

/*
 * SELECTORS FOR PQ
 */
defined('S360_LPA_SELECTOR_LOGIN_INPUT') || define('S360_LPA_SELECTOR_LOGIN_INPUT', 'input[name=login], form#new_customer input[type="submit"]');
defined('S360_LPA_SELECTOR_PAY_BUTTON') || define('S360_LPA_SELECTOR_PAY_BUTTON', '#form_cart .proceed_to_checkout, #basket_checkout');
defined('S360_LPA_SELECTOR_PAY_BUTTON_DURING_CHECKOUT') || define('S360_LPA_SELECTOR_PAY_BUTTON_DURING_CHECKOUT', '#bestellvorgang');


defined('S360_LPA_SELECTOR_LOGIN_INPUT_MOBILE') || define('S360_LPA_SELECTOR_LOGIN_INPUT_MOBILE', 'input[name=login], form#new_customer input[type="submit"]');
defined('S360_LPA_SELECTOR_PAY_BUTTON_MOBILE') || define('S360_LPA_SELECTOR_PAY_BUTTON_MOBILE', '#form_cart .proceed_to_checkout, #basket_checkout');
defined('S360_LPA_SELECTOR_PAY_BUTTON_DURING_CHECKOUT_MOBILE') || define('S360_LPA_SELECTOR_PAY_BUTTON_DURING_CHECKOUT_MOBILE', '#bestellvorgang');

/*
 * FIXED OTHER CONSTANTS
 */
defined('S360_LPA_LOGIN_REDIRECT_COOKIE') || define('S360_LPA_LOGIN_REDIRECT_COOKIE', 'lpa_redirect');
defined('S360_LPA_AUTHORIZATION_TIMEOUT_DEFAULT') || define('S360_LPA_AUTHORIZATION_TIMEOUT_DEFAULT', 1440);
defined('S360_LPA_ACCESS_TOKEN_COOKIE') || define('S360_LPA_ACCESS_TOKEN_COOKIE', 'amazon_Login_accessToken');
defined('S360_LPA_ADDRESS_CONSENT_TOKEN_COOKIE') || define('S360_LPA_ADDRESS_CONSENT_TOKEN_COOKIE', 'lpa_address_consent_token');
defined('S360_LPA_BACKOFF_MAX_RETRIES') || define('S360_LPA_BACKOFF_MAX_RETRIES', 5);
defined('S360_LPA_ADAPTER_MODE_BACKEND') || define('S360_LPA_ADAPTER_MODE_BACKEND', 'backend');
defined('S360_LPA_ADMIN_ORDERS_PER_PAGE') || define('S360_LPA_ADMIN_ORDERS_PER_PAGE', 200);

/*
 * AMAZON PAYMENTS STATUS AND REASONS
 */
defined('S360_LPA_STATUS_DRAFT') || define('S360_LPA_STATUS_DRAFT', 'Draft');
defined('S360_LPA_STATUS_OPEN') || define('S360_LPA_STATUS_OPEN', 'Open');
defined('S360_LPA_STATUS_CLOSED') || define('S360_LPA_STATUS_CLOSED', 'Closed');
defined('S360_LPA_STATUS_CANCELED') || define('S360_LPA_STATUS_CANCELED', 'Canceled');
defined('S360_LPA_STATUS_SUSPENDED') || define('S360_LPA_STATUS_SUSPENDED', 'Suspended');
defined('S360_LPA_STATUS_PENDING') || define('S360_LPA_STATUS_PENDING', 'Pending');
defined('S360_LPA_STATUS_DECLINED') || define('S360_LPA_STATUS_DECLINED', 'Declined');
defined('S360_LPA_STATUS_COMPLETED') || define('S360_LPA_STATUS_COMPLETED', 'Completed');
/*
 * AMAZON PAYMENTS REASON CODES
 */
defined('S360_LPA_REASON_INVALID_PAYMENT_METHOD') || define('S360_LPA_REASON_INVALID_PAYMENT_METHOD', 'InvalidPaymentMethod');
defined('S360_LPA_REASON_SELLER_CANCELED') || define('S360_LPA_REASON_SELLER_CANCELED', 'SellerCanceled');
defined('S360_LPA_REASON_STALE') || define('S360_LPA_REASON_STALE', 'Stale');
defined('S360_LPA_REASON_AMAZON_CANCELED') || define('S360_LPA_REASON_AMAZON_CANCELED', 'AmazonCanceled');
defined('S360_LPA_REASON_EXPIRED') || define('S360_LPA_REASON_EXPIRED', 'Expired');
defined('S360_LPA_REASON_MAX_AMOUNT_CHARGED') || define('S360_LPA_REASON_MAX_AMOUNT_CHARGED', 'MaxAmountCharged');
defined('S360_LPA_REASON_MAX_AUTHORIZATIONS_CAPTURED') || define('S360_LPA_REASON_MAX_AUTHORIZATIONS_CAPTURED', 'MaxAuthorizationsCaptured');
defined('S360_LPA_REASON_AMAZON_CLOSED') || define('S360_LPA_REASON_AMAZON_CLOSED', 'AmazonClosed');
defined('S360_LPA_REASON_SELLER_CLOSED') || define('S360_LPA_REASON_SELLER_CLOSED', 'SellerClosed');
defined('S360_LPA_REASON_AMAZON_REJECTED') || define('S360_LPA_REASON_AMAZON_REJECTED', 'AmazonRejected');
defined('S360_LPA_REASON_PROCESSING_FAILURE') || define('S360_LPA_REASON_PROCESSING_FAILURE', 'ProcessingFailure');
defined('S360_LPA_REASON_TRANSACTION_TIMED_OUT') || define('S360_LPA_REASON_TRANSACTION_TIMED_OUT', 'TransactionTimedOut');
defined('S360_LPA_REASON_EXPIRED_UNUSED') || define('S360_LPA_REASON_EXPIRED_UNUSED', 'ExpiredUnused');
defined('S360_LPA_REASON_MAX_CAPTURES_PROCESSED') || define('S360_LPA_REASON_MAX_CAPTURES_PROCESSED', 'MaxCapturesProcessed');
defined('S360_LPA_REASON_ORDER_REFERENCE_CANCELED') || define('S360_LPA_REASON_ORDER_REFERENCE_CANCELED', 'OrderReferenceCanceled');
defined('S360_LPA_REASON_MAX_AMOUNT_REFUNDED') || define('S360_LPA_REASON_MAX_AMOUNT_REFUNDED', 'MaxAmountRefunded');
defined('S360_LPA_REASON_MAX_REFUNDS_PROCESSED') || define('S360_LPA_REASON_MAX_REFUNDS_PROCESSED', 'MaxRefundsProcessed');

