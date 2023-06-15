<?php

namespace MyFatoorah\Library\API;

use MyFatoorah\Library\MyFatoorah;
use Exception;

/**
 * MyFatoorahList handles the list process of MyFatoorah API endpoints
 *
 * @author    MyFatoorah <tech@myfatoorah.com>
 * @copyright 2021 MyFatoorah, All rights reserved
 * @license   GNU General Public License v3.0
 */
class MyFatoorahList extends MyFatoorah
{
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Gets the rate of a given currency according to the default currency of the MyFatoorah portal account.
     *
     * @param string $currency The currency that will be converted into the currency of MyFatoorah portal account.
     *
     * @return number       The conversion rate converts a given currency to the MyFatoorah account default currency.
     *
     * @throws Exception    Throw exception if the input currency is not support by MyFatoorah portal account.
     */
    public function getCurrencyRate($currency)
    {

        $json = $this->getCurrencyRates();
        foreach ($json as $value) {
            if ($value->Text == $currency) {
                return $value->Value;
            }
        }
        throw new Exception('The selected currency is not supported by MyFatoorah');
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
