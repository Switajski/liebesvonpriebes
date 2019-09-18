CREATE TABLE xplugin_s360_amazon_lpa_shop4_tconfig (
    cName VARCHAR(255) NOT NULL,
    cWert VARCHAR(255) NOT NULL
);
CREATE TABLE xplugin_s360_amazon_lpa_shop4_taccountmapping (
    kKunde INT(10) NOT NULL,
    cAmazonId VARCHAR(255) NOT NULL,
    nVerifiziert INT(1) NOT NULL DEFAULT 0,
    cVerifizierungsCode VARCHAR(255)
);
CREATE TABLE xplugin_s360_amazon_lpa_shop4_torder (
    kBestellung INT(10) NOT NULL,
    cOrderReferenceId VARCHAR(50) NOT NULL,
    cOrderStatus VARCHAR(50),
    cOrderStatusReason VARCHAR(50),
    fOrderAmount DECIMAL(18,2),
    cOrderCurrencyCode VARCHAR(50),
    nOrderExpirationTimestamp INT,
    bSandbox INT(1) NOT NULL
);
CREATE TABLE xplugin_s360_amazon_lpa_shop4_tauthorization (
    cOrderReferenceId VARCHAR(50) NOT NULL,
    cAuthorizationId VARCHAR(50) NOT NULL,
    cAuthorizationStatus VARCHAR(50),
    cAuthorizationStatusReason VARCHAR(50),
    fAuthorizationAmount DECIMAL(18,2),
    cAuthorizationCurrencyCode VARCHAR(50),
    fCapturedAmount DECIMAL(18,2),
    cCapturedCurrencyCode VARCHAR(50),
    bCaptureNow INT(1) NOT NULL,
    nAuthorizationExpirationTimestamp INT,
    bSandbox INT(1) NOT NULL
);
CREATE TABLE xplugin_s360_amazon_lpa_shop4_tcapture (
    cAuthorizationId VARCHAR(50) NOT NULL,
    cCaptureId VARCHAR(50) NOT NULL,
    cCaptureStatus VARCHAR(50),
    cCaptureStatusReason VARCHAR(50),
    fCaptureAmount DECIMAL(18,2),
    cCaptureCurrencyCode VARCHAR(50),
    fRefundedAmount DECIMAL(18,2),
    cRefundedCurrencyCode VARCHAR(50),
    bSandbox INT(1) NOT NULL
);
CREATE TABLE xplugin_s360_amazon_lpa_shop4_trefund (
    cCaptureId VARCHAR(50) NOT NULL,
    cRefundId VARCHAR(50) NOT NULL,
    cRefundStatus VARCHAR(50),
    cRefundStatusReason VARCHAR(50),
    cRefundType VARCHAR(50),
    fRefundAmount DECIMAL(18,2),
    cRefundCurrencyCode VARCHAR(50),
    bSandbox INT(1) NOT NULL
);
CREATE TABLE xplugin_s360_amazon_lpa_shop4_tcron (
    cCronId VARCHAR(50) NOT NULL DEFAULT 'LPA-CRON',
    nLastRunTimestamp INT
);