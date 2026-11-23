<?php

namespace MyFatoorah\Test;

use MyFatoorah\Library\MyFatoorah;

class MyFatoorahConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $shipped;

    /**
     * @var int
     */
    private $shippedTime;

    /**
     * @var string
     */
    private $tmp;

    //-----------------------------------------------------------------------------------------------------------------------------------------
    protected function setUp(): void
    {
        $this->shipped = dirname(__DIR__) . '/src/mf-config.json';

        // Hold the file at today's date, so reading it never calls the portal,
        // and put the date back in tearDown so the library refreshes as usual.
        $this->shippedTime = (int) filemtime($this->shipped);
        touch($this->shipped);
        clearstatcache(true, $this->shipped);

        $this->tmp = sys_get_temp_dir() . '/mf-config-test-' . getmypid();
        if (!is_dir($this->tmp)) {
            mkdir($this->tmp, 0755, true);
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    protected function tearDown(): void
    {
        touch($this->shipped, $this->shippedTime);

        if (is_dir($this->tmp)) {
            @chmod($this->tmp, 0755);
            foreach ((array) glob($this->tmp . '/*') as $file) {
                @chmod((string) $file, 0644);
                @unlink((string) $file);
            }
            @rmdir($this->tmp);
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testTheShippedConfigurationIsReadable(): void
    {
        $countries = MyFatoorah::getMFCountries();

        $this->assertNotEmpty($countries);
        $this->assertArrayHasKey('KWT', $countries);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testEveryCountryCarriesTheKeysTheLibraryReads(): void
    {
        foreach (MyFatoorah::getMFCountries() as $code => $country) {
            $this->assertArrayHasKey('v2', $country, $code);
            $this->assertArrayHasKey('testv2', $country, $code);
            $this->assertArrayHasKey('portal', $country, $code);
            $this->assertArrayHasKey('testPortal', $country, $code);
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testAFreshFileIsReadWithoutCallingThePortal(): void
    {
        $before = filemtime($this->shipped);

        MyFatoorah::getMFCountries();
        clearstatcache(true, $this->shipped);

        // An update always touches the file, so an unchanged date means none happened.
        $this->assertSame($before, filemtime($this->shipped));
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testAReadOnlyFileStopsTheUpdateBeforeThePortalIsCalled(): void
    {
        $file = $this->tmp . '/mf-config.json';
        file_put_contents($file, '{"KWT":{"v2":"x"}}');
        chmod($file, 0444);

        if (is_writable($file)) {
            $this->markTestSkipped('The user can write the file anyway, so there is nothing to check.');
        }

        $this->assertFalse(ConfigProbe::update($file));
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testAMissingFileInAReadOnlyFolderStopsTheLibrary(): void
    {
        chmod($this->tmp, 0555);

        if (is_writable($this->tmp)) {
            $this->markTestSkipped('The user can write the folder anyway, so there is nothing to check.');
        }

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('no permission to write');
        ConfigProbe::update($this->tmp . '/mf-config.json');
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testAFailureIsOnlyReportedWhileAFileIsCached(): void
    {
        $this->assertFalse(ConfigProbe::report('the portal said no', $this->tmp . '/mf-config.json', true));
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testAFailureWithNothingCachedStopsWithTheReason(): void
    {
        $file = $this->tmp . '/mf-config.json';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('the portal said no');
        ConfigProbe::report('the portal said no', $file, false);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testAnUpdateWritesAConfigurationThatParses(): void
    {
        $file = $this->tmp . '/mf-config.json';

        // With no file to fall back on a failed call stops the library, so the
        // unreachable portal is caught here and reported as a skip, not a break.
        try {
            $updated = ConfigProbe::update($file);
        } catch (\Exception $e) {
            $this->markTestSkipped('The portal could not be reached: ' . $e->getMessage());
        }

        $this->assertTrue($updated);

        $written = json_decode((string) file_get_contents($file), true);

        $this->assertIsArray($written);
        $this->assertNotEmpty($written);
        $this->assertArrayHasKey('KWT', $written);
    }
}
