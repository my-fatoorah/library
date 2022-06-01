<?php

namespace MyFatoorah\Test;

use MyFatoorah\Library\PaymentMyfatoorahApiV2;

class PaymentMyfatoorahApiV2Test extends \PHPUnit\Framework\TestCase {

    private $keys;

//-----------------------------------------------------------------------------------------------------------------------------------------
    public function __construct() {
        parent::__construct();
        $this->keys = include ('apiKeys.php');
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetVendorGateways() {

        foreach ($this->keys as $token) {
            try {
                $mfObj = new PaymentMyfatoorahApiV2($token['apiKey'], $token['countryMode'], $token['isTest']);
                $json  = $mfObj->getVendorGateways();

                $this->assertEquals('AMEX', $json[0]->PaymentMethodEn);
            } catch (\Exception $ex) {
                $this->assertEquals($token['exception'], $ex->getMessage(), $token['message']);
            }
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * change the accessibility of a function
     * usage $method->invokeArgs($mfObj, [$ua]);
     * 
     * @param type $name
     * @return type
     */
    protected static function getMethod($name) {
        $class  = new \ReflectionClass('\MyFatoorah\Library\PaymentMyfatoorahApiV2');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * https://www.whatismybrowser.com/guides/the-latest-user-agent/firefox
     */
    public function testGetBrowserNameFirefox() {

        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:98.0) Gecko/20100101 Firefox/98.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 12.3; rv:98.0) Gecko/20100101 Firefox/98.0',
            'Mozilla/5.0 (X11; Linux i686; rv:98.0) Gecko/20100101 Firefox/98.0',
            'Mozilla/5.0 (Linux x86_64; rv:98.0) Gecko/20100101 Firefox/98.0',
            'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:98.0) Gecko/20100101 Firefox/98.0',
            'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:98.0) Gecko/20100101 Firefox/98.0',
            'Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:98.0) Gecko/20100101 Firefox/98.0',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 12_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/98.0 Mobile/15E148 Safari/605.1.15',
            'Mozilla/5.0 (iPad; CPU OS 12_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/98.0 Mobile/15E148 Safari/605.1.15',
            'Mozilla/5.0 (iPod touch; CPU iPhone OS 12_3 like Mac OS X) AppleWebKit/604.5.6 (KHTML, like Gecko) FxiOS/98.0 Mobile/15E148 Safari/605.1.15',
            'Mozilla/5.0 (Android 12; Mobile; rv:68.0) Gecko/68.0 Firefox/98.0',
            'Mozilla/5.0 (Android 12; Mobile; LG-M255; rv:98.0) Gecko/98.0 Firefox/98.0',
            'Mozilla/5.0 (X11; Linux i686; rv:91.0) Gecko/20100101 Firefox/91.0',
            'Mozilla/5.0 (Linux x86_64; rv:91.0) Gecko/20100101 Firefox/91.0',
            'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:91.0) Gecko/20100101 Firefox/91.0',
            'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:91.0) Gecko/20100101 Firefox/91.0',
            'Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:91.0) Gecko/20100101 Firefox/91.0'
        ];

        foreach ($userAgents as $ua) {
            $expected = PaymentMyfatoorahApiV2::getBrowserName($ua);
            $this->assertEquals('Firefox', $expected);
        }
    }

    public function testGetBrowserNameSafari() {

        $userAgents = [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 12_3) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.3 Safari/605.1.15',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 15_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.3 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPad; CPU OS 15_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.3 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPod touch; CPU iPhone 15_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.3 Mobile/15E148 Safari/604.1'
        ];

        foreach ($userAgents as $ua) {
            $expected = PaymentMyfatoorahApiV2::getBrowserName($ua);
            $this->assertEquals('Safari', $expected);
        }
    }

    public function testGetBrowserNameChrome() {

        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.82 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.82 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.82 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 12_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.82 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.82 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 15_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/99.0.4844.59 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPad; CPU OS 15_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/99.0.4844.59 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPod; CPU iPhone OS 15_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/99.0.4844.59 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Linux; Android 10) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.73 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 10; SM-A205U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.73 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 10; SM-A102U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.73 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 10; SM-G960U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.73 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 10; SM-N960U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.73 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 10; LM-Q720) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.73 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 10; LM-X420) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.73 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 10; LM-Q710(FGN)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.73 Mobile Safari/537.36'
        ];

        foreach ($userAgents as $ua) {
            $expected = PaymentMyfatoorahApiV2::getBrowserName($ua);
            $this->assertEquals('Chrome', $expected);
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
}
