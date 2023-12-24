<?php

namespace MyFatoorah\Test\API;

use MyFatoorah\Library\API\MyFatoorahSupplier;
use PHPUnit\Framework\TestCase;

class MyFatoorahSupplierTest extends TestCase
{

    private $keys;

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
        $this->keys = include __DIR__ . '/../apiKeys.php';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetSupplierDashboard()
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
    public function testIsSupplierApproved()
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
    public function testIsSupplierApprovedNotCreated()
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
