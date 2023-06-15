<?php

namespace MyFatoorah\Library\API;

use MyFatoorah\Library\MyFatoorah;

/**
 * MyFatoorahSupplier handles the Supplier process of MyFatoorah API endpoints
 *
 * @author    MyFatoorah <tech@myfatoorah.com>
 * @copyright 2021 MyFatoorah, All rights reserved
 * @license   GNU General Public License v3.0
 */
class MyFatoorahSupplier extends MyFatoorah
{
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Gets Supplier Dashboard information if Supplier exists in the MyFatoorah portal account.
     *
     * @param int $supplierCode The supplier code that exists in MyFatoorah portal account.
     *
     * @return object
     */
    public function getSupplierDashboard($supplierCode)
    {
        $url = $this->apiURL . '/v2/GetSupplierDashboard?SupplierCode=' . $supplierCode;
        return $this->callAPI($url, null, null, "Get Supplier Documents");
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Returns the supplier status in MyFatoorah account
     *
     * @param int $supplierCode The supplier code that exists in MyFatoorah portal account.
     *
     * @return boolean
     */
    public function isSupplierApproved($supplierCode)
    {
        $supplier = $this->getSupplierDashboard($supplierCode);
        return ($supplier->IsApproved && $supplier->IsActive);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
