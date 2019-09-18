<?php
/**
 * Reset Payment Flags
 *
 * @author aj
 * @created Wed, 17 Feb 2016 11:54:35 +0100
 */

/**
 * Class Migration_20160217115435
 */
class Migration_20160217115435 extends Migration implements IMigration
{
    protected $author = 'aj';

    public function up()
    {
        $items = [
            'za_ueberweisung_jtl'         => 0,
            'za_nachnahme_jtl'            => 0,
            'za_kreditkarte_jtl'          => 0,
            'za_rechnung_jtl'             => 0,
            'za_lastschrift_jtl'          => 0,
            'za_barzahlung_jtl'           => 0,
            'za_paypal_jtl'               => 0,
            'za_mbqc_wlt_jtl'             => 0,
            'za_worldpay_jtl'             => 0,
            'za_ipayment_jtl'             => 0,
            'za_sofortueberweisung_jtl'   => 0,
            'za_safetypay'                => 0,
            'za_postfinance_jtl'          => 0,
            'za_saferpay_jtl'             => 0,
            'za_wirecard_jtl'             => 0,
            'za_eos_dd_jtl'               => 0,
            'za_mbqc_acc_jtl'             => 0,
            'za_mbqc_did_jtl'             => 0,
            'za_mbqc_git_jtl'             => 0,
            'za_mbqc_sft_jtl'             => 0,
            'za_mbqc_msc_jtl'             => 0,
            'za_mbqc_vsa_jtl'             => 0,
            'za_mbqc_mae_jtl'             => 0,
            'za_mbqc_idl_jtl'             => 0,
            'za_mbqc_gcb_jtl'             => 0,
            'za_mbqc_csi_jtl'             => 0,
            'za_mbqc_dnk_jtl'             => 0,
            'za_mbqc_ebt_jtl'             => 0,
            'za_mbqc_ent_jtl'             => 0,
            'za_mbqc_lsr_jtl'             => 0,
            'za_mbqc_npy_jtl'             => 0,
            'za_mbqc_pli_jtl'             => 0,
            'za_mbqc_psp_jtl'             => 0,
            'za_mbqc_pwy_jtl'             => 0,
            'za_mbqc_slo_jtl'             => 0,
            'za_mbqc_so2_jtl'             => 0,
            'za_dresdnercetelem_jtl'      => 0,
            'za_clickandbuy_jtl'          => 0,
            'za_mbqc_obt_jtl'             => 0,
            'za_billpay_jtl'              => 1,
            'za_eos_cc_jtl'               => 1,
            'za_eos_direct_jtl'           => 0,
            'za_eos_ewallet_jtl'          => 1,
            'za_billpay_invoice_jtl'      => 1,
            'za_billpay_direct_debit_jtl' => 1,
            'za_billpay_rate_payment_jtl' => 1,
            'za_billpay_paylater_jtl'     => 1,
            '%\\_paypalplus'              => 1,
            '%\\_paypalexpress'           => 1,
            '%\\_paypalbasic'             => 0
        ];

        foreach ($items as $cModulId => $nWaehrendBestellung) {
            $this->execute("UPDATE tzahlungsart SET nWaehrendBestellung={$nWaehrendBestellung} WHERE cModulId LIKE '{$cModulId}'");
        }
    }

    public function down()
    {
    }
}
