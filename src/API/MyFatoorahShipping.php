<?php

namespace MyFatoorah\Library\API;

use MyFatoorah\Library\MyFatoorah;

/**
 * MyFatoorahShipping handles the shipping process of MyFatoorah API endpoints
 *
 * @author    MyFatoorah <tech@myfatoorah.com>
 * @copyright 2021 MyFatoorah, All rights reserved
 * @license   GNU General Public License v3.0
 */
class MyFatoorahShipping extends MyFatoorah
{
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get MyFatoorah Shipping Countries (GET API)
     *
     * @return array
     */
    public function getShippingCountries()
    {
        $url  = "$this->apiURL/v2/GetCountries";
        $json = $this->callAPI($url, null, null, 'Get Countries');
        return $json->Data;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get Shipping Cities (GET API)
     *
     * @param int    $method      [1 for DHL, 2 for Aramex]
     * @param string $countryCode It can be obtained from getShippingCountries
     * @param string $searchValue The key word that will be used in searching
     *
     * @return array
     */
    public function getShippingCities($method, $countryCode, $searchValue = '')
    {
        $url = $this->apiURL . '/v2/GetCities'
                . '?shippingMethod=' . $method
                . '&countryCode=' . $countryCode
                . '&searchValue=' . urlencode(substr($searchValue, 0, 30));

        $json = $this->callAPI($url, null, null, "Get Cities: $countryCode");
        return array_map('ucwords', $json->Data->CityNames);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Calculate Shipping Charge (POST API)
     *
     * @param array $curlData the curl data contains the shipping information
     *
     * @return object
     */
    public function calculateShippingCharge($curlData)
    {
        $url  = "$this->apiURL/v2/CalculateShippingCharge";
        $json = $this->callAPI($url, $curlData, null, 'Calculate Shipping Charge');
        return $json->Data;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
