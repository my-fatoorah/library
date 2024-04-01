<?php

namespace MyFatoorah\Library\API\Payment;

use MyFatoorah\Library\API\MyFatoorahList;

/**
 *  MyFatoorahPaymentForm handles the form process of MyFatoorah API endpoints
 *
 * @author    MyFatoorah <tech@myfatoorah.com>
 * @copyright MyFatoorah, All rights reserved
 * @license   GNU General Public License v3.0
 */
class MyFatoorahPaymentEmbedded extends MyFatoorahPayment
{

    /**
     * The checkoutGateways array is used to display the payment in the checkout page.
     *
     * @var array
     */
    protected static $checkoutGateways;

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * List available Payment Methods
     *
     * @param double|int $invoiceAmount  The display invoice total amount.
     * @param string     $currencyIso    The display invoice currency ISO.
     * @param bool       $isApRegistered Is site domain is registered with ApplePay and MyFatoorah or not.
     *
     * @return array
     */
    public function getCheckoutGateways($invoiceAmount, $currencyIso, $isApRegistered)
    {

        if (!empty(self::$checkoutGateways)) {
            return self::$checkoutGateways;
        }

        $gateways = $this->initiatePayment($invoiceAmount, $currencyIso);

        $mfListObj    = new MyFatoorahList($this->config);
        $allRates     = $mfListObj->getCurrencyRates();
        $currencyRate = MyFatoorahList::getOneCurrencyRate($currencyIso, $allRates);

        self::$checkoutGateways = ['all' => [], 'cards' => [], 'form' => [], 'ap' => [], 'gp' => []];
        foreach ($gateways as $gateway) {
            $gateway->PaymentTotalAmount = $this->getPaymentTotalAmount($gateway, $allRates, $currencyRate);

            $gateway->GatewayData = [
                'GatewayTotalAmount'   => number_format($gateway->PaymentTotalAmount, 2),
                'GatewayCurrency'      => $gateway->PaymentCurrencyIso,
                'GatewayTransCurrency' => self::getTranslatedCurrency($gateway->PaymentCurrencyIso),
            ];

            self::$checkoutGateways = $this->addGatewayToCheckout($gateway, self::$checkoutGateways, $isApRegistered);
        }

        if ($isApRegistered) {
            //add only one ap gateway
            self::$checkoutGateways['ap'] = $this->getOneEmbeddedGateway(self::$checkoutGateways['ap'], $currencyIso, $allRates);
        }

        return self::$checkoutGateways;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Calculate the amount value that will be paid in each payment method
     * 
     * @param object $paymentMethod The payment method object obtained from the initiate payment endpoint
     * @param array  $allRates      The MyFatoorah currency rate array of all gateways.
     * @param double $currencyRate  The currency rate of the invoice.
     * 
     * @return double
     */
    private function getPaymentTotalAmount($paymentMethod, $allRates, $currencyRate)
    {

        $dbTrucVal = ((int) ($paymentMethod->TotalAmount * 1000)) / 1000;
        if ($paymentMethod->PaymentCurrencyIso == $paymentMethod->CurrencyIso) {
            return ceil($dbTrucVal * 100) / 100;
        }

        //convert to portal base currency
        $dueVal          = ($currencyRate == 1) ? $dbTrucVal : round($paymentMethod->TotalAmount / $currencyRate, 3);
        $baseTotalAmount = ceil($dueVal * 100) / 100;

        //gateway currency is not the portal currency
        $paymentCurrencyRate = MyFatoorahList::getOneCurrencyRate($paymentMethod->PaymentCurrencyIso, $allRates);
        if ($paymentCurrencyRate != 1) {
            return ceil($baseTotalAmount * $paymentCurrencyRate * 100) / 100;
        }

        return $baseTotalAmount;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Returns One Apple pay array in case multiple are enabled in the account
     *
     * @param array  $gateways        The all available AP/GP gateways
     * @param string $displayCurrency The currency of the invoice total amount.
     * @param array  $allRates        The MyFatoorah currency rate array of all gateways.
     *
     * @return array
     */
    private function getOneEmbeddedGateway($gateways, $displayCurrency, $allRates)
    {

        $displayCurrencyIndex = array_search($displayCurrency, array_column($gateways, 'PaymentCurrencyIso'));
        if ($displayCurrencyIndex) {
            return $gateways[$displayCurrencyIndex];
        }

        //get defult mf account currency
        $defCurKey       = array_search('1', array_column($allRates, 'Value'));
        $defaultCurrency = $allRates[$defCurKey]->Text;

        $defaultCurrencyIndex = array_search($defaultCurrency, array_column($gateways, 'PaymentCurrencyIso'));
        if ($defaultCurrencyIndex) {
            return $gateways[$defaultCurrencyIndex];
        }

        if (isset($gateways[0])) {
            return $gateways[0];
        }

        return [];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Returns the translation of the currency ISO code
     *
     * @param string $currency currency ISO code
     *
     * @return array
     */
    public static function getTranslatedCurrency($currency)
    {

        $currencies = [
            'KWD' => ['en' => 'KD', 'ar' => 'د.ك'],
            'SAR' => ['en' => 'SR', 'ar' => 'ريال'],
            'BHD' => ['en' => 'BD', 'ar' => 'د.ب'],
            'EGP' => ['en' => 'LE', 'ar' => 'ج.م'],
            'QAR' => ['en' => 'QR', 'ar' => 'ر.ق'],
            'OMR' => ['en' => 'OR', 'ar' => 'ر.ع'],
            'JOD' => ['en' => 'JD', 'ar' => 'د.أ'],
            'AED' => ['en' => 'AED', 'ar' => 'د'],
            'USD' => ['en' => 'USD', 'ar' => 'دولار'],
            'EUR' => ['en' => 'EUR', 'ar' => 'يورو']
        ];

        return $currencies[$currency] ?? ['en' => '', 'ar' => ''];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
