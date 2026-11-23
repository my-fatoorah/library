<?php

namespace MyFatoorah\Library\API;

use MyFatoorah\Library\MyFatoorah;
use Exception;

class MyFatoorahList extends MyFatoorah
{
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Gets the rate of a given currency according to the default currency of the MyFatoorah portal account.
     *
     * @param string $currency The currency that will be converted into the currency of MyFatoorah portal account.
     * @param array  $allRates An array of MyFatoorah currencies and rates
     *
     * @return float
     *
     * @throws Exception        Throw exception if the input currency is not support by MyFatoorah portal account.
     */
    public static function getOneCurrencyRate($currency, $allRates)
    {
        foreach ($allRates as $value) {
            if ($value->Text == $currency) {
                return (float) $value->Value;
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
     * @return float
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
