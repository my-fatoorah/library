<?php

namespace MyFatoorah\Library\API\Payment;

use MyFatoorah\Library\API\MyFatoorahList;

/**
 *  MyFatoorahPaymentForm handles the form process of MyFatoorah API endpoints
 *
 * @author    MyFatoorah <tech@myfatoorah.com>
 * @copyright 2021 MyFatoorah, All rights reserved
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
     * @param double|int $invoiceValue       Total invoice amount.
     * @param string     $displayCurrencyIso Total invoice currency.
     * @param bool       $isAppleRegistered  Is site domain is registered with applePay and MyFatoorah or not.
     *
     * @return array
     */
    public function getCheckoutGateways($invoiceValue, $displayCurrencyIso, $isAppleRegistered)
    {

        if (!empty(self::$checkoutGateways)) {
            return self::$checkoutGateways;
        }

        $gateways = $this->initiatePayment($invoiceValue, $displayCurrencyIso);

        $mfListObj    = new MyFatoorahList($this->config);
        $allRates     = $mfListObj->getCurrencyRates();
        $currencyRate = $mfListObj->getCurrencyRate($displayCurrencyIso, $allRates);

        self::$checkoutGateways = ['all' => [], 'cards' => [], 'form' => [], 'ap' => [], 'gp' => []];
        foreach ($gateways as $gateway) {
            $gateway->PaymentTotalAmount = $this->getPaymentTotalAmount($gateway, $allRates, $currencyRate);

            $gateway->GatewayData = [
                'GatewayTotalAmount' => number_format($gateway->PaymentTotalAmount, 2),
                'GatewayCurrency'    => $gateway->PaymentCurrencyIso
            ];

            self::$checkoutGateways = $this->addGatewayToCheckoutGateways($gateway, self::$checkoutGateways, $isAppleRegistered);
        }

        if ($isAppleRegistered) {
            //add only one ap gateway
            self::$checkoutGateways['ap'] = $this->getOneApplePayGateway(self::$checkoutGateways['ap'], $displayCurrencyIso, $allRates);
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

        if ($paymentMethod->PaymentCurrencyIso == $paymentMethod->CurrencyIso) {
            return $paymentMethod->TotalAmount;
        }

        //convert to portal base currency
        $baseTotalAmount = ceil(((int) ($paymentMethod->TotalAmount * 100)) / $currencyRate) / 100;

        //gateway currency is not the portal currency
        $mfListObj           = new MyFatoorahList($this->config);
        $paymentCurrencyRate = $mfListObj->getCurrencyRate($paymentMethod->PaymentCurrencyIso, $allRates);
        if ($paymentCurrencyRate != 1) {
            return ceil($baseTotalAmount * $paymentCurrencyRate * 100) / 100;
        }

        return $baseTotalAmount;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Returns One Apple pay array in case multiple are enabled in the account
     *
     * @param array  $apGateways      The all available AP gateways
     * @param string $displayCurrency The currency of the invoice total amount.
     * @param array  $allRates        The MyFatoorah currency rate array of all gateways.
     *
     * @return array
     */
    protected function getOneApplePayGateway($apGateways, $displayCurrency, $allRates)
    {

        $displayCurrencyIndex = array_search($displayCurrency, array_column($apGateways, 'PaymentCurrencyIso'));
        if ($displayCurrencyIndex) {
            return $apGateways[$displayCurrencyIndex];
        }

        //get defult mf account currency
        $defCurKey       = array_search('1', array_column($allRates, 'Value'));
        $defaultCurrency = $allRates[$defCurKey]->Text;

        $defaultCurrencyIndex = array_search($defaultCurrency, array_column($apGateways, 'PaymentCurrencyIso'));
        if ($defaultCurrencyIndex) {
            return $apGateways[$defaultCurrencyIndex];
        }

        if (isset($apGateways[0])) {
            return $apGateways[0];
        }

        return [];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
