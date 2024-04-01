<?php

namespace MyFatoorah\Library\API;

use MyFatoorah\Library\MyFatoorah;
use Exception;

/**
 * MyFatoorahList handles the list process of MyFatoorah API endpoints
 *
 * @author    MyFatoorah <tech@myfatoorah.com>
 * @copyright MyFatoorah, All rights reserved
 * @license   GNU General Public License v3.0
 */
class MyFatoorahList extends MyFatoorah
{
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Gets the rate of a given currency according to the default currency of the MyFatoorah portal account.
     * 
     * @param string $currency The currency that will be converted into the currency of MyFatoorah portal account.
     * @param array  $allRates An array of MyFatoorah currencies and rates
     *
     * @return double
     *
     * @throws Exception        Throw exception if the input currency is not support by MyFatoorah portal account.
     */
    public static function getOneCurrencyRate($currency, $allRates)
    {

        foreach ($allRates as $value) {
            if ($value->Text == $currency) {
                return (double) $value->Value;
            }
        }
        throw new Exception('The selected currency is not supported by MyFatoorah');
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Gets the rate of a given currency according to the default currency of the MyFatoorah portal account.
     *
     * @param string $currency The currency that will be converted into the currency of MyFatoorah portal account.
     *
     * @return double
     */
    public function getCurrencyRate($currency)
    {

        $allRates = $this->getCurrencyRates();
        return self::getOneCurrencyRate($currency, $allRates);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get list of MyFatoorah currency rates
     *
     * @return array
     */
    public function getCurrencyRates()
    {

        $url = "$this->apiURL/v2/GetCurrenciesExchangeList";
        return (array) $this->callAPI($url, null, null, 'Get Currencies Exchange List');
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
