<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'browsererkennung.php';
require_once PFAD_ROOT . PFAD_PHPQUERY . 'phpquery.class.php';

/**
 * Class JTLSmarty
 */
class JTLSmarty extends SmartyBC
{
    /**
     * @var JTLCache
     */
    public $jtlCache;

    /**
     * @var array
     */
    public $config;

    /**
     * @var array
     */
    public $_cache_include_info;

    /**
     * @var Template
     */
    public $template;

    /**
     * @var JTLSmarty|null
     */
    public static $_instance;

    /**
     * @var string
     */
    public $context = 'frontend';

    /**
     * @var array
     */
    private static $_replacer = [
        'productdetails/index.tpl'                              => 'artikel.tpl',
        'productwizard/index.tpl'                               => 'auswahlassistent.tpl',
        'checkout/order_completed.tpl'                          => 'bestellabschluss.tpl',
        'checkout/index.tpl'                                    => 'bestellvorgang.tpl',
        'productdetails/review_form.tpl'                        => 'bewertung_formular.tpl',
        'account/index.tpl'                                     => 'jtl.tpl',
        'contact/index.tpl'                                     => 'kontakt.tpl',
        'blog/index.tpl'                                        => 'news.tpl',
        'newsletter/index.tpl'                                  => 'newsletter.tpl',
        'account/password.tpl'                                  => 'passwort_vergessen.tpl',
        'checkout/download_popup.tpl'                           => 'popup.tpl',
        'register/index.tpl'                                    => 'registrieren.tpl',
        'layout/index.tpl'                                      => 'seite.tpl',
        'productlist/index.tpl'                                 => 'suche.tpl',
        'productdetails/recommendation.tpl'                     => 'tpl_inc/artikel_artikelweiterempfehlenformular.tpl',
        'productdetails/reviews.tpl'                            => 'tpl_inc/artikel_bewertung.tpl',
        'productdetails/review_item.tpl'                        => 'tpl_inc/artikel_bewertung_kommentar.tpl',
        'productdetails/download.tpl'                           => 'tpl_inc/artikel_downloads.tpl',
        'productdetails/finance.tpl'                            => 'tpl_inc/artikel_finanzierung.tpl',
        'productdetails/finance_popup.tpl'                      => 'tpl_inc/artikel_finanzierung_popup.tpl',
        'productdetails/question_on_item.tpl'                   => 'tpl_inc/artikel_fragezumproduktformular.tpl',
        'productdetails/pushed.tpl'                             => 'tpl_inc/artikel_hinzugefuegt.tpl',
        'productdetails/details.tpl'                            => 'tpl_inc/artikel_inc.tpl',
        'productdetails/actions.tpl'                            => 'tpl_inc/artikel_inc_aktionen.tpl',
        'productdetails/attributes.tpl'                         => 'tpl_inc/artikel_inc_attribute.tpl',
        'productdetails/image.tpl'                              => 'tpl_inc/artikel_inc_bild.tpl',
        'productdetails/bundle.tpl'                             => 'tpl_inc/artikel_inc_bundle.tpl',
        'productdetails/form.tpl'                               => 'tpl_inc/artikel_inc_form.tpl',
        'productdetails/stock.tpl'                              => 'tpl_inc/artikel_inc_lagerbestand.tpl',
        'productdetails/rating.tpl'                             => 'tpl_inc/artikel_inc_stars.tpl',
        'productdetails/tabs.tpl'                               => 'tpl_inc/artikel_inc_tabs.tpl',
        'productdetails/tags.tpl'                               => 'tpl_inc/artikel_inc_tags.tpl',
        'productdetails/variation.tpl'                          => 'tpl_inc/artikel_inc_variationen.tpl',
        'productdetails/variation_value.tpl'                    => 'tpl_inc/artikel_inc_variationen_wert.tpl',
        'productdetails/basket.tpl'                             => 'tpl_inc/artikel_inc_warenkorb.tpl',
        'productlist/item_box.tpl'                              => 'tpl_inc/artikel_item_box.tpl',
        'productlist/item_list.tpl'                             => 'tpl_inc/artikel_item_list.tpl',
        'productdetails/config.tpl'                             => 'tpl_inc/artikel_konfigurator.tpl',
        'productdetails/config_summary.tpl'                     => 'tpl_inc/artikel_konfigurator_summary.tpl',
        'productdetails/matrix.tpl'                             => 'tpl_inc/artikel_matrix.tpl',
        'productdetails/mediafile.tpl'                          => 'tpl_inc/artikel_mediendatei.tpl',
        'productdetails/popups.tpl'                             => 'tpl_inc/artikel_popups.tpl',
        'productdetails/price.tpl'                              => 'tpl_inc/artikel_preis.tpl',
        'productdetails/price_history.tpl'                      => 'tpl_inc/artikel_preisverlauf.tpl',
        'productdetails/availability_notification_form.tpl'     => 'tpl_inc/artikel_produktverfuegbarformular.tpl',
        'productdetails/slider.tpl'                             => 'tpl_inc/artikel_slider.tpl',
        'productdetails/upload.tpl'                             => 'tpl_inc/artikel_uploads.tpl',
        'productdetails/variation_dependencies.tpl'             => 'tpl_inc/artikel_variations_abhaengigkeiten.tpl',
        'productdetails/warehouse.tpl'                          => 'tpl_inc/artikel_warenlager.tpl',
        'productdetails/redirect.tpl'                           => 'tpl_inc/artikel_weiterleitung.tpl',
        'productwizard/form.tpl'                                => 'tpl_inc/auswahlassistent_inc.tpl',
        'account/retrospective_payment.tpl'                     => 'tpl_inc/bestellab_again_zusatzschritt.tpl',
        'checkout/conversion_tracking.tpl'                      => 'tpl_inc/bestellabschluss_conversion_tracking.tpl',
        'checkout/inc_order_completed.tpl'                      => 'tpl_inc/bestellabschluss_fertig.tpl',
        'checkout/inc_trustedshops_excellence.tpl'              => 'tpl_inc/bestellabschluss_trustedshops.tpl',
        'checkout/inc_paymentmodules.tpl'                       => 'tpl_inc/bestellabschluss_weiterleitung.tpl',
        'checkout/step0_login_or_register.tpl'                  => 'tpl_inc/bestellvorgang_accountwahl.tpl',
        'checkout/step5_confirmation.tpl'                       => 'tpl_inc/bestellvorgang_bestaetigung.tpl',
        'checkout/step2_delivery_address.tpl'                   => 'tpl_inc/bestellvorgang_lieferadresse.tpl',
        'checkout/inc_order_items.tpl'                          => 'tpl_inc/bestellvorgang_positionen.tpl',
        'checkout/inc_steps.tpl'                                => 'tpl_inc/bestellvorgang_steps.tpl',
        'checkout/step1_proceed_as_guest.tpl'                   => 'tpl_inc/bestellvorgang_unregistriert_formular.tpl',
        'checkout/step3_shipping_options.tpl'                   => 'tpl_inc/bestellvorgang_versand.tpl',
        'checkout/step4_payment_options.tpl'                    => 'tpl_inc/bestellvorgang_zahlung.tpl',
        'checkout/step4_payment_additional.tpl'                 => 'tpl_inc/bestellvorgang_zahlung_zusatzschritt.tpl',
        'checkout/modules/creditcard.tpl'                       => 'tpl_inc/bestellvorgang_zahlungsart_kreditkarte.tpl',
        'checkout/modules/direct_debit.tpl'                     => 'tpl_inc/bestellvorgang_zahlungsart_lastschrift.tpl',
        'checkout/step6_init_payment.tpl'                       => 'tpl_inc/bestellvorgang_zahlungsvorgang.tpl',
        'boxes/box_bestseller.tpl'                              => 'tpl_inc/boxes/box_bestseller.tpl',
        'boxes/box_container.tpl'                               => 'tpl_inc/boxes/box_container.tpl',
        'boxes/box_custom.tpl'                                  => 'tpl_inc/boxes/box_eigene.tpl',
        'boxes/box_custom_empty.tpl'                            => 'tpl_inc/boxes/box_eigene_leer.tpl',
        'boxes/box_coming_soon.tpl'                             => 'tpl_inc/boxes/box_erscheinende_produkte.tpl',
        'boxes/box_filter_rating.tpl'                           => 'tpl_inc/boxes/box_filter_bewertung.tpl',
        'boxes/box_filter_characteristics.tpl'                  => 'tpl_inc/boxes/box_filter_merkmale.tpl',
        'boxes/box_filter_pricerange.tpl'                       => 'tpl_inc/boxes/box_filter_preisspanne.tpl',
        'boxes/box_filter_search.tpl'                           => 'tpl_inc/boxes/box_filter_suche.tpl',
        'boxes/box_filter_search_special.tpl'                   => 'tpl_inc/boxes/box_filter_suchspecial.tpl',
        'boxes/box_filter_tag.tpl'                              => 'tpl_inc/boxes/box_filter_tag.tpl',
        'boxes/box_characteristics_global.tpl'                  => 'tpl_inc/boxes/box_globale_merkmale.tpl',
        'boxes/box_manufacturers.tpl'                           => 'tpl_inc/boxes/box_hersteller.tpl',
        'boxes/box_info.tpl'                                    => 'tpl_inc/boxes/box_informationen.tpl',
        'boxes/box_categories.tpl'                              => 'tpl_inc/boxes/box_kategorien.tpl',
        'boxes/box_config.tpl'                                  => 'tpl_inc/boxes/box_konfig.tpl',
        'boxes/box_linkgroups.tpl'                              => 'tpl_inc/boxes/box_linkgruppe.tpl',
        'boxes/box_login.tpl'                                   => 'tpl_inc/boxes/box_login.tpl',
        'boxes/box_new_in_stock.tpl'                            => 'tpl_inc/boxes/box_neu_im_sortiment.tpl',
        'boxes/box_news_categories.tpl'                         => 'tpl_inc/boxes/box_news_kategorien.tpl',
        'boxes/box_news_month.tpl'                              => 'tpl_inc/boxes/box_news_monat.tpl',
        'boxes/box_priceradar.tpl'                              => 'tpl_inc/boxes/box_preisradar.tpl',
        'boxes/box_direct_purchase.tpl'                         => 'tpl_inc/boxes/box_schnelleinkauf.tpl',
        'boxes/box_special_offer.tpl'                           => 'tpl_inc/boxes/box_sonderangebote.tpl',
        'boxes/box_search_cloud.tpl'                            => 'tpl_inc/boxes/box_suchwolke.tpl',
        'boxes/box_tag_cloud.tpl'                               => 'tpl_inc/boxes/box_tagwolke.tpl',
        'boxes/box_top_offer.tpl'                               => 'tpl_inc/boxes/box_top_angebot.tpl',
        'boxes/box_top_rated.tpl'                               => 'tpl_inc/boxes/box_top_bewertet.tpl',
        'boxes/box_trustedshops_reviews.tpl'                    => 'tpl_inc/boxes/box_trustedshops_kundenbewertung.tpl',
        'boxes/box_trustedshops_seal.tpl'                       => 'tpl_inc/boxes/box_trustedshops_siegel.tpl',
        'boxes/box_poll.tpl'                                    => 'tpl_inc/boxes/box_umfrage.tpl',
        'boxes/box_comparelist.tpl'                             => 'tpl_inc/boxes/box_vergleichsliste.tpl',
        'boxes/box_basket.tpl'                                  => 'tpl_inc/boxes/box_warenkorb.tpl',
        'boxes/box_wishlist.tpl'                                => 'tpl_inc/boxes/box_wunschliste.tpl',
        'boxes/box_last_seen.tpl'                               => 'tpl_inc/boxes/box_zuletzt_angesehen.tpl',
        'snippets/categories_recursive.tpl'                     => 'tpl_inc/categories_recursive.tpl',
        'snippets/filter/review.tpl'                            => 'tpl_inc/filter/filter_bewertung.tpl',
        'snippets/filter/characteristic.tpl'                    => 'tpl_inc/filter/filter_merkmale.tpl',
        'snippets/filter/pricerange.tpl'                        => 'tpl_inc/filter/filter_preisspanne.tpl',
        'snippets/filter/search.tpl'                            => 'tpl_inc/filter/filter_suche.tpl',
        'snippets/filter/special.tpl'                           => 'tpl_inc/filter/filter_suchspecial.tpl',
        'snippets/filter/tag.tpl'                               => 'tpl_inc/filter/filter_tag.tpl',
        'layout/footer.tpl'                                     => 'tpl_inc/footer.tpl',
        'layout/header.tpl'                                     => 'tpl_inc/header.tpl',
        'layout/breadcrumb.tpl'                                 => 'tpl_inc/inc_breadcrumb.tpl',
        'snippets/extension.tpl'                                => 'tpl_inc/inc_extension.tpl',
        'checkout/coupon_form.tpl'                              => 'tpl_inc/inc_kupon_guthaben.tpl',
        'checkout/inc_delivery_address.tpl'                     => 'tpl_inc/inc_lieferadresse.tpl',
        'checkout/inc_billing_address.tpl'                      => 'tpl_inc/inc_rechnungsadresse.tpl',
        'productlist/result_options.tpl'                        => 'tpl_inc/inc_result_options.tpl',
        'layout/footnotes.tpl'                                  => 'tpl_inc/inc_seite.tpl',
        'snippets/trustbadge.tpl'                               => 'tpl_inc/inc_trustedshops.tpl',
        'account/delete_account.tpl'                            => 'tpl_inc/jtl_account_loeschen.tpl',
        'account/order_details.tpl'                             => 'tpl_inc/jtl_bestellung.tpl',
        'account/order_item.tpl'                                => 'tpl_inc/jtl_bestellung_position.tpl',
        'account/downloads.tpl'                                 => 'tpl_inc/jtl_downloads.tpl',
        'account/customers_recruiting.tpl'                      => 'tpl_inc/jtl_kundenwerbenkunden.tpl',
        'account/login.tpl'                                     => 'tpl_inc/jtl_login.tpl',
        'account/my_account.tpl'                                => 'tpl_inc/jtl_meinkonto.tpl',
        'account/change_password.tpl'                           => 'tpl_inc/jtl_passwort_aendern.tpl',
        'account/address_form.tpl'                              => 'tpl_inc/jtl_rechnungsdaten.tpl',
        'account/uploads.tpl'                                   => 'tpl_inc/jtl_uploads.tpl',
        'account/wishlist.tpl'                                  => 'tpl_inc/jtl_wunschliste.tpl',
        'account/wishlist_email_form.tpl'                       => 'tpl_inc/jtl_wunschliste_emailversand.tpl',
        'checkout/inc_billing_address_form.tpl'                 => 'tpl_inc/kundenformular.tpl',
        'snippets/linkgroup_list.tpl'                           => 'tpl_inc/linkgroup_list.tpl',
        'checkout/modules/billpay/bestellabschluss.tpl'         => 'tpl_inc/modules/billpay/bestellabschluss.tpl',
        'checkout/modules/billpay/paylater.tpl'                 => 'tpl_inc/modules/billpay/paylater.tpl',
        'checkout/modules/billpay/raten.tpl'                    => 'tpl_inc/modules/billpay/raten.tpl',
        'checkout/modules/billpay/zusatzschritt.tpl'            => 'tpl_inc/modules/billpay/zusatzschritt.tpl',
        'checkout/modules/eos/bestellabschluss.tpl'             => 'tpl_inc/modules/eos/bestellabschluss.tpl',
        'checkout/modules/eos/eos.css'                          => 'tpl_inc/modules/eos/eos.css',
        'checkout/modules/heidelpay/bestellabschluss.tpl'       => 'tpl_inc/modules/heidelpay/bestellabschluss.tpl',
        'checkout/modules/iclear/bestellabschluss.tpl'          => 'tpl_inc/modules/iclear/bestellabschluss.tpl',
        'checkout/modules/invoice.tpl'                          => 'tpl_inc/modules/invoice.tpl',
        'checkout/modules/moneybookers_qc/bestellabschluss.tpl' => 'tpl_inc/modules/moneybookers_qc/bestellabschluss.tpl',
        'checkout/modules/paymentpartner/bestellabschluss.tpl'  => 'tpl_inc/modules/paymentpartner/bestellabschluss.tpl',
        'checkout/modules/paypal/bestellabschluss.tpl'          => 'tpl_inc/modules/paypal/bestellabschluss.tpl',
        'checkout/modules/postfinance/bestellabschluss.tpl'     => 'tpl_inc/modules/postfinance/bestellabschluss.tpl',
        'checkout/modules/uos/bestellabschluss.tpl'             => 'tpl_inc/modules/uos/bestellabschluss.tpl',
        'checkout/modules/ut/bestellabschluss.tpl'              => 'tpl_inc/modules/ut/bestellabschluss.tpl',
        'checkout/modules/wirecard/bestellabschluss.tpl'        => 'tpl_inc/modules/wirecard/bestellabschluss.tpl',
        'blog/details.tpl'                                      => 'tpl_inc/news_detailansicht.tpl',
        'blog/month_overview.tpl'                               => 'tpl_inc/news_monatsuebersicht.tpl',
        'blog/overview.tpl'                                     => 'tpl_inc/news_uebersicht.tpl',
        'account/download_preview.tpl'                          => 'tpl_inc/popup_download_vorschau.tpl',
        'register/form.tpl'                                     => 'tpl_inc/registrieren_formular.tpl',
        'page/404.tpl'                                          => 'tpl_inc/seite_404.tpl',
        'page/free_gift.tpl'                                    => 'tpl_inc/seite_gratisgeschenk.tpl',
        'page/manufacturers.tpl'                                => 'tpl_inc/seite_hersteller.tpl',
        'page/livesearch.tpl'                                   => 'tpl_inc/seite_livesuche.tpl',
        'page/news_archive.tpl'                                 => 'tpl_inc/seite_newsarchiv.tpl',
        'page/newsletter_archive.tpl'                           => 'tpl_inc/seite_newsletterarchiv.tpl',
        'page/sitemap.tpl'                                      => 'tpl_inc/seite_sitemap.tpl',
        'page/index.tpl'                                        => 'tpl_inc/seite_startseite.tpl',
        'page/tagging.tpl'                                      => 'tpl_inc/seite_tagging.tpl',
        'page/shipping.tpl'                                     => 'tpl_inc/seite_versand.tpl',
        'productlist/bestseller.tpl'                            => 'tpl_inc/suche_bestseller.tpl',
        'productlist/financing.tpl'                             => 'tpl_inc/suche_finanzierung.tpl',
        'productlist/footer.tpl'                                => 'tpl_inc/suche_footer.tpl',
        'productlist/header.tpl'                                => 'tpl_inc/suche_header.tpl',
        'poll/progress.tpl'                                     => 'tpl_inc/umfrage_durchfuehren.tpl',
        'poll/result.tpl'                                       => 'tpl_inc/umfrage_ergebnis.tpl',
        'poll/overview.tpl'                                     => 'tpl_inc/umfrage_uebersicht.tpl',
        'basket/cart_dropdown.tpl'                              => 'tpl_inc/warenkorb_mini.tpl',
        'basket/cart_dropdown_label.tpl'                        => 'tpl_inc/warenkorb_mini_label.tpl',
        'poll/index.tpl'                                        => 'umfrage.tpl',
        'comparelist/index.tpl'                                 => 'vergleichsliste.tpl',
        'basket/index.tpl'                                      => 'warenkorb.tpl',
        'snippets/maintenance.tpl'                              => 'wartung.tpl',
        'snippets/wishlist.tpl'                                 => 'wunschliste.tpl'
    ];

    /**
     * @var int
     */
    public $_file_perms = 0664;

    /**
     * @var bool
     */
    public static $isChildTemplate = false;

    /**
     * modified constructor with custom initialisation
     *
     * @param bool   $fast_init - set to true when init from backend to avoid setting session data
     * @param bool   $isAdmin
     * @param bool   $tplCache
     * @param string $context
     */
    public function __construct($fast_init = false, $isAdmin = false, $tplCache = true, $context = 'frontend')
    {
        parent::__construct();
        Smarty::$_CHARSET = JTL_CHARSET;
        if (defined('SMARTY_USE_SUB_DIRS') && is_bool(SMARTY_USE_SUB_DIRS)) {
            $this->setUseSubDirs(SMARTY_USE_SUB_DIRS);
        }
        $this->setErrorReporting(SMARTY_LOG_LEVEL)
             ->setForceCompile(SMARTY_FORCE_COMPILE ? true : false)
             ->setDebugging(SMARTY_DEBUG_CONSOLE ? true : false);

        $this->config = Shop::getSettings([CONF_TEMPLATE, CONF_CACHING, CONF_GLOBAL]);
        $template     = $isAdmin ? AdminTemplate::getInstance() : Template::getInstance();
        $cTemplate    = $template->getDir();
        $parent       = null;
        if ($isAdmin === false) {
            $parent      = $template->getParent();
            $_compileDir = PFAD_ROOT . PFAD_COMPILEDIR . $cTemplate . '/';
            if (!file_exists($_compileDir)) {
                mkdir($_compileDir);
            }
            $templatePaths[$this->context] = PFAD_ROOT . PFAD_TEMPLATES . $cTemplate . '/';
            $pluginTemplatePaths           = Plugin::getTemplatePaths();
            foreach ($pluginTemplatePaths as $moduleId => $path) {
                $templateKey                 = 'plugin_' . $moduleId;
                $templatePaths[$templateKey] = $path;
            }
            $this->setTemplateDir($templatePaths)
                 ->setCompileDir($_compileDir)
                 ->setCacheDir(PFAD_ROOT . PFAD_COMPILEDIR . $cTemplate . '/' . 'page_cache/')
                 ->setPluginsDir(SMARTY_PLUGINS_DIR);

            if ($parent !== null) {
                self::$isChildTemplate = true;
                $this->addTemplateDir(PFAD_ROOT . PFAD_TEMPLATES . $parent, $parent . '/')
                     ->assign('parent_template_path', PFAD_ROOT . PFAD_TEMPLATES . $parent . '/')
                     ->assign('parentTemplateDir', PFAD_TEMPLATES . $parent . '/');
            }
        } else {
            $_compileDir = PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR;
            if (!file_exists($_compileDir)) {
                mkdir($_compileDir);
            }
            $this->context = 'backend';
            $this->setCaching(false)
                 ->setDebugging(SMARTY_DEBUG_CONSOLE ? true : false)
                 ->setTemplateDir([$this->context => PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES . $cTemplate])
                 ->setCompileDir($_compileDir)
                 ->setConfigDir(PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES . $cTemplate . '/lang/')
                 ->setPluginsDir(SMARTY_PLUGINS_DIR)
                 ->configLoad('german.conf', 'global');
            unset($this->config['caching']['page_cache']);
        }
        $this->template = $template;

        if ($fast_init === false) {
            if (FIX_REGEX_ENCODING === true) {
                mb_regex_encoding(JTL_CHARSET);
            }
            $this->registerPlugin('function', 'lang', [$this, '__gibSprachWert'])
                 ->registerPlugin('modifier', 'replace_delim', [$this, 'replaceDelimiters'])
                 ->registerPlugin('modifier', 'count_characters', [$this, 'countCharacters'])
                 ->registerPlugin('modifier', 'string_format', [$this, 'stringFormat'])
                 ->registerPlugin('modifier', 'string_date_format', [$this, 'dateFormat'])
                 ->registerPlugin('modifier', 'truncate', [$this, 'truncate']);

            if ($isAdmin === false) {
                $this->cache_lifetime = (isset($cacheOptions['expiration']) && ((int)$cacheOptions['expiration'] > 0))
                    ? $cacheOptions['expiration']
                    : 86400;
                //assign variables moved from $_SESSION to cache to smarty
                $linkHelper = LinkHelper::getInstance();
                $linkGroups = $linkHelper->getLinkGroups();
                if ($linkGroups === null) {
                    //this can happen when there is a $_SESSION active and object cache is being flushed
                    //since setzeLinks() is only executed in class.core.Session.php
                    $linkGroups = setzeLinks();
                }
                require_once PFAD_ROOT . PFAD_CLASSES . 'class.helper.Hersteller.php';
                $manufacturerHelper = HerstellerHelper::getInstance();
                $manufacturers      = $manufacturerHelper->getManufacturers();
                $this->assign('linkgroups', $linkGroups)
                     ->assign('manufacturers', $manufacturers);
                $this->template_class = 'jtlTplClass';
            }
            if (!$isAdmin) {
                $this->setCachingParams($this->config);
            }
            $_tplDir = $this->getTemplateDir($this->context);
            global $smarty;
            $smarty = $this;
            if (file_exists($_tplDir . 'php/functions_custom.php')) {
                require_once $_tplDir . 'php/functions_custom.php';
            } elseif (file_exists($_tplDir . 'php/functions.php')) {
                require_once $_tplDir . 'php/functions.php';
            } elseif ($parent !== null && file_exists(PFAD_ROOT . PFAD_TEMPLATES . $parent . '/php/functions.php')) {
                require_once PFAD_ROOT . PFAD_TEMPLATES . $parent . '/php/functions.php';
            }
        }
        if ($context === 'frontend' || $context === 'backend') {
            self::$_instance = $this;
        }
    }

    /**
     * set options
     *
     * @param array|null $config
     * @return $this
     */
    public function setCachingParams($config = null)
    {
        //instantiate new cache - we use different options here
        if ($config === null) {
            $config = Shop::getSettings([CONF_CACHING]);
        }
        $compileCheck = !(isset($config['caching']['compile_check']) && $config['caching']['compile_check'] === 'N');
        $this->setCaching(self::CACHING_OFF)
             ->setCompileCheck($compileCheck);

        return $this;
    }

    /**
     * @param bool $fast_init
     * @param bool $isAdmin
     * @return JTLSmarty|null
     */
    public static function getInstance($fast_init = false, $isAdmin = false)
    {
        return (self::$_instance === null) ? new self($fast_init, $isAdmin) : self::$_instance;
    }

    /**
     * phpquery output filter
     *
     * @param string $tplOutput
     * @return string
     */
    public function __outputFilter($tplOutput)
    {
        $hookList = Plugin::getHookList();
        $isMobile = $this->template->isMobileTemplateActive();
        if ($isMobile ||
            (isset($hookList[HOOK_SMARTY_OUTPUTFILTER]) &&
                is_array($hookList[HOOK_SMARTY_OUTPUTFILTER]) &&
                count($hookList[HOOK_SMARTY_OUTPUTFILTER]) > 0)
        ) {
            $this->unregisterFilter('output', [$this, '__outputFilter']);
            $GLOBALS['doc'] = phpQuery::newDocumentHTML($tplOutput, JTL_CHARSET);
            if ($isMobile) {
                executeHook(HOOK_SMARTY_OUTPUTFILTER_MOBILE);
            } else {
                executeHook(HOOK_SMARTY_OUTPUTFILTER);
            }
            $tplOutput = $GLOBALS['doc']->htmlOuter();
        }
        if (isset($this->config['template']['general']['minify_html'])
            && $this->config['template']['general']['minify_html'] === 'Y'
        ) {
            $minifyCSS = (isset($this->config['template']['general']['minify_html_css'])
                && $this->config['template']['general']['minify_html_css'] === 'Y');
            $minifyJS  = (isset($this->config['template']['general']['minify_html_js'])
                && $this->config['template']['general']['minify_html_js'] === 'Y');
            $tplOutput = $this->minify_html($tplOutput, $minifyCSS, $minifyJS);
        }

        return $tplOutput;
    }

    /**
     * @param null|string $template
     * @param null|string $cache_id
     * @param null|string $compile_id
     * @param null $parent
     * @return bool
     */
    public function isCached($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        return false;
    }

    /**
     * @param bool $mode
     * @return $this
     */
    public function setCaching($mode)
    {
        $this->caching = $mode;

        return $this;
    }

    /**
     * @param bool $mode
     * @return $this
     */
    public function setDebugging($mode)
    {
        $this->debugging = $mode;

        return $this;
    }

    /**
     * html minification
     *
     * @param string $html
     * @param bool   $minifyCSS
     * @param bool   $minifyJS
     * @return string
     */
    private function minify_html($html, $minifyCSS = false, $minifyJS = false)
    {
        require_once PFAD_ROOT . PFAD_MINIFY . '/lib/Minify/Loader.php';
        Minify_Loader::register();
        $options = [];
        if ($minifyCSS === true) {
            $options['cssMinifier'] = ['Minify_CSS', 'minify'];
        }
        if ($minifyJS === true) {
            $options['jsMinifier'] = ['JSMin', 'minify'];
        }
        $minify = new Minify_HTML($html, $options);
        try {
            $res = $minify->process();
        } catch (JSMin_UnterminatedStringException $e) {
            $res = $html;
        }

        return $res;
    }

    /**
     * translation
     *
     * @param array                    $params
     * @param Smarty_Internal_Template $template
     * @return void|string
     */
    public function __gibSprachWert($params, Smarty_Internal_Template $template)
    {
        $cValue = '';
        if (!isset($params['section'])) {
            $params['section'] = 'global';
        }
        if (isset($params['section'], $params['key'])) {
            $cValue = Shop::Lang()->get($params['key'], $params['section']);
            // Für vsprintf ein String der :: exploded wird
            if (isset($params['printf']) && strlen($params['printf']) > 0) {
                $cValue = vsprintf($cValue, explode(':::', $params['printf']));
            }
        }
        if (SMARTY_SHOW_LANGKEY) {
            $cValue = '#' . $params['section'] . '.' . $params['key'] . '#';
        }
        if (isset($params['assign'])) {
            $template->assign($params['assign'], $cValue);
        } else {
            return $cValue;
        }
    }

    /**
     * @param string $text
     * @return int
     */
    public function countCharacters($text)
    {
        return strlen($text);
    }

    /**
     * @param string $string
     * @param string $format
     * @return string
     */
    public function stringFormat($string, $format)
    {
        return sprintf($format, $string);
    }

    /**
     * @param string $string
     * @param string $format
     * @param string $default_date
     * @return string
     */
    public function dateFormat($string, $format = '%b %e, %Y', $default_date = '')
    {
        if ($string !== '') {
            $timestamp = smarty_make_timestamp($string);
        } elseif ($default_date !== '') {
            $timestamp = smarty_make_timestamp($default_date);
        } else {
            return $string;
        }
        if (DIRECTORY_SEPARATOR === '\\') {
            $_win_from = ['%D', '%h', '%n', '%r', '%R', '%t', '%T'];
            $_win_to   = ['%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S'];
            if (strpos($format, '%e') !== false) {
                $_win_from[] = '%e';
                $_win_to[]   = sprintf('%\' 2d', date('j', $timestamp));
            }
            if (strpos($format, '%l') !== false) {
                $_win_from[] = '%l';
                $_win_to[]   = sprintf('%\' 2d', date('h', $timestamp));
            }
            $format = str_replace($_win_from, $_win_to, $format);
        }

        return strftime($format, $timestamp);
    }

    /**
     * @param string $string
     * @param int    $length
     * @param string $etc
     * @param bool   $break_words
     * @param bool   $middle
     * @return mixed|string
     */
    public function truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false)
    {
        $length = (int)$length;
        if ($length === 0) {
            return '';
        }
        if (strlen($string) > $length) {
            $length -= min($length, strlen($etc));
            if (!$break_words && !$middle) {
                $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
            }

            return !$middle
                ? substr($string, 0, $length) . $etc
                : substr($string, 0, $length / 2) . $etc . substr($string, -$length / 2);
        }

        return $string;
    }

    /**
     * @param string $cText
     * @return string
     */
    public function replaceDelimiters($cText)
    {
        $cReplace = $this->config['global']['global_dezimaltrennzeichen_sonstigeangaben'];
        if ($cReplace !== ',' || $cReplace !== '.' || $cReplace === '') {
            $cReplace = ',';
        }

        return str_replace('.', $cReplace, $cText);
    }

    /**
     * @param string $cFilename
     * @return string
     */
    public function getCustomFile($cFilename)
    {
        if (self::$isChildTemplate === true ||
            !isset($this->config['template']['general']['use_customtpl']) ||
            $this->config['template']['general']['use_customtpl'] !== 'Y'
        ) {
            //disabled on child templates for now
            return $cFilename;
        }
        $cFile       = basename($cFilename, '.tpl');
        $cSubPath    = dirname($cFilename);
        $cCustomFile = (strpos($cSubPath, PFAD_ROOT) === false)
            ? $this->getTemplateDir($this->context) . (($cSubPath === '.')
                ? ''
                : ($cSubPath . '/')) . $cFile . '_custom.tpl'
            : ($cSubPath . '/' . $cFile . '_custom.tpl');

        return file_exists($cCustomFile) ? $cCustomFile : $cFilename;
    }

    /**
     * @param string $cFilename
     * @return string
     */
    public function getFallbackFile($cFilename)
    {
        if (
            !self::$isChildTemplate &&
            TEMPLATE_COMPATIBILITY === true &&
            isset(self::$_replacer[$cFilename]) &&
            !file_exists($this->getTemplateDir($this->context) . $cFilename)
        ) {
            $cFilename = self::$_replacer[$cFilename];
        }

        return $cFilename;
    }

    /**
     * fetches a rendered Smarty template
     *
     * @param  string $template   the resource handle of the template file or template object
     * @param  mixed  $cache_id   cache id to be used with this template
     * @param  mixed  $compile_id compile id to be used with this template
     * @param  object $parent     next higher level of Smarty variables
     *
     * @throws Exception
     * @throws SmartyException
     * @return string rendered template output
     */
    public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        $_debug = (!empty($this->_debug->template_data))
            ? $this->_debug->template_data
            : null;
        $res = parent::fetch($this->getResourceName($template), $cache_id, $compile_id, $parent);
        if ($_debug !== null) {
            //fetch overwrites the old debug data so we have to merge it with our previously saved data
            $this->_debug->template_data = array_merge($_debug, $this->_debug->template_data);
        }

        return $res;
    }

    /**
     * displays a Smarty template
     *
     * @param string $template   the resource handle of the template file or template object
     * @param mixed  $cache_id   cache id to be used with this template
     * @param mixed  $compile_id compile id to be used with this template
     * @param object $parent     next higher level of Smarty variables
     */
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        if ($this->context === 'frontend') {
            $this->registerFilter('output', [$this, '__outputFilter']);
        }

        return parent::display($this->getResourceName($template), $cache_id, $compile_id, $parent);
    }

    /**
     * generates a unique cache id for every given resource
     *
     * @param string      $resource_name
     * @param array       $conditions
     * @param string|null $cache_id
     * @return null|string
     */
    public function getCacheID($resource_name, $conditions, $cache_id = null)
    {
        return null;
    }

    /**
     * @param string $resource_name
     * @return string
     */
    public function getResourceName($resource_name)
    {
        $transform = false;
        if (strpos($resource_name, 'string:') === 0) {
            return $resource_name;
        }
        if (strpos($resource_name, 'file:') === 0) {
            $resource_name = str_replace('file:', '', $resource_name);
            $transform     = true;
        }
        $resource_custom_name   = $this->getCustomFile($resource_name);
        $resource_fallback_name = $this->getFallbackFile($resource_custom_name);
        $resource_cfb_name      = $this->getCustomFile($resource_fallback_name);

        executeHook(HOOK_SMARTY_FETCH_TEMPLATE, [
            'original'  => &$resource_name,
            'custom'    => &$resource_custom_name,
            'fallback'  => &$resource_fallback_name,
            'out'       => &$resource_cfb_name,
            'transform' => $transform
        ]);

        if ($this->context === 'frontend'
            && $resource_name === $resource_cfb_name
            && file_exists($this->getTemplateDir('frontend') . $resource_cfb_name)
        ) {
            $pluginTemplateExtends = [];

            foreach (Plugin::getTemplatePaths() as $moduleId => $pluginTemplatePath) {
                $templateKey = 'plugin_' . $moduleId;
                $templateVar = 'oPlugin_' . $moduleId;

                if ($this->getTemplateVars($templateVar) === null) {
                    $oPlugin = Plugin::getPluginById($moduleId);
                    $this->assign($templateVar, $oPlugin);
                }

                $file        = $this->_realpath($pluginTemplatePath . $resource_cfb_name, true);
                if (file_exists($file)) {
                    $pluginTemplateExtends[] = sprintf('[%s]%s', $templateKey, $resource_cfb_name);
                }
            }

            if (count($pluginTemplateExtends) > 0) {
                $transform         = false;
                $resource_cfb_name = sprintf(
                    'extends:[frontend]%s|%s',
                    $resource_cfb_name,
                    implode('|', $pluginTemplateExtends)
                );
            }
        }

        return $transform ? ('file:' . $resource_cfb_name) : $resource_cfb_name;
    }

    /**
     * @param bool $use_sub_dirs
     * @return $this
     */
    public function setUseSubDirs($use_sub_dirs)
    {
        parent::setUseSubDirs($use_sub_dirs);

        return $this;
    }

    /**
     * @param bool $force_compile
     * @return $this
     */
    public function setForceCompile($force_compile)
    {
        parent::setForceCompile($force_compile);

        return $this;
    }

    /**
     * @param bool $compile_check
     * @return $this
     */
    public function setCompileCheck($compile_check)
    {
        parent::setCompileCheck($compile_check);

        return $this;
    }

    /**
     * @param int $error_reporting
     * @return $this
     */
    public function setErrorReporting($error_reporting)
    {
        parent::setErrorReporting($error_reporting);

        return $this;
    }

    /**
     * @return bool
     */
    public static function getIsChildTemplate()
    {
        return self::$isChildTemplate;
    }
}

/**
 * Class jtlTplClass
 */
class jtlTplClass extends Smarty_Internal_Template
{
    /**
     * @var JTLSmarty
     */
    public $smarty;

    /**
     * Runtime function to render sub-template
     *
     * @param string  $template       template name
     * @param mixed   $cache_id       cache id
     * @param mixed   $compile_id     compile id
     * @param integer $caching        cache mode
     * @param integer $cache_lifetime life time of cache data
     * @param array   $data           passed parameter template variables
     * @param int     $scope          scope in which {include} should execute
     * @param bool    $forceTplCache  cache template object
     * @param string  $uid            file dependency uid
     * @param string  $content_func   function name
     *
     */
    public function _subTemplateRender(
        $template,
        $cache_id,
        $compile_id,
        $caching,
        $cache_lifetime,
        $data,
        $scope,
        $forceTplCache,
        $uid = null,
        $content_func = null
    ) {
        return parent::_subTemplateRender(
            $this->smarty->getResourceName($template),
            $cache_id,
            $compile_id,
            $caching,
            $cache_lifetime,
            $data,
            $scope,
            $forceTplCache,
            $uid,
            $content_func
        );
    }

    /**
     * @param bool $no_output_filter
     * @param null|int $display
     * @return string
     */
    public function render($no_output_filter = true, $display = null)
    {
        if ($no_output_filter === false && $display !== 1) {
            $no_output_filter = true;
        }

        return parent::render($no_output_filter, $display);
    }
}
