<?php

namespace MyFatoorah\Test\API;

use MyFatoorah\Test\MyFatoorahTestCase;
use MyFatoorah\Library\API\MyFatoorahSupplier;

class MyFatoorahSupplierTest extends MyFatoorahTestCase
{
    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetSupplierDashboard(): void
    {
        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahSupplier($config);
                $data  = $mfObj->getSupplierDashboard(1);

                $this->assertArrayHasKey('TotalAwaitingBalance', (array) $data);
            } catch (\Exception $ex) {
                $exception = $config['supplierException'] ?? $config['exception'];
                $this->assertEquals($exception, $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testIsSupplierApproved(): void
    {
        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahSupplier($config);
                $data  = $mfObj->isSupplierApproved(1);

                $this->assertTrue($data);
            } catch (\Exception $ex) {
                $exception = $config['supplierException'] ?? $config['exception'];
                $this->assertEquals($exception, $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testIsSupplierApprovedNotCreated(): void
    {
        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahSupplier($config);
                $data  = $mfObj->isSupplierApproved(3232323);

                $this->assertTrue($data);
            } catch (\Exception $ex) {
                $exception = $config['supplierException'] ?? $config['exception'];
                $this->assertEquals($exception, $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
