<?php

namespace MyFatoorah\Test;

use MyFatoorah\Library\MyFatoorah;

class MyFatoorahHelperTest extends \PHPUnit\Framework\TestCase
{

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetPhone()
    {
        $expected = MyFatoorah::getPhone('');
        $this->assertEquals('', $expected[0]);
        $this->assertEquals('', $expected[1]);

        $expected1 = MyFatoorah::getPhone('+2 01234567890');
        $this->assertEquals('201', $expected1[0]);
        $this->assertEquals('234567890', $expected1[1]);

        $expected2 = MyFatoorah::getPhone('+201234567890');
        $this->assertEquals('201', $expected2[0]);
        $this->assertEquals('234567890', $expected2[1]);

        $expected3 = MyFatoorah::getPhone('00201234567890');
        $this->assertEquals('201', $expected3[0]);
        $this->assertEquals('234567890', $expected3[1]);

        $expected4 = MyFatoorah::getPhone('002031234567');
        $this->assertEquals('203', $expected4[0]);
        $this->assertEquals('1234567', $expected4[1]);

        $expected5 = MyFatoorah::getPhone('٠٠٢٠١٢٣٤٥٦٧٨٩٠');
        $this->assertEquals('201', $expected5[0]);
        $this->assertEquals('234567890', $expected5[1]);
    }

    public function testGetPhoneException1()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Phone Number lenght must be between 3 to 14 digits');
        MyFatoorah::getPhone('12');
    }

    public function testGetPhoneException2()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Phone Number lenght must be between 3 to 14 digits');
        MyFatoorah::getPhone('12345678910123456');
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetWeightRate()
    {
        $expected1 = MyFatoorah::getWeightRate('KG');
        $this->assertEquals(1, $expected1);

        $expected2 = MyFatoorah::getWeightRate('kg');
        $this->assertEquals(1, $expected2);

        $expected3 = MyFatoorah::getWeightRate('oZ');
        $this->assertEquals(0.0283495, $expected3);
    }

    public function testGetWeightRateException1()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Weight units must be in kg, g, lbs, or oz. Default is kg');
        MyFatoorah::getWeightRate('');
    }

    public function testGetWeightRateException2()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Weight units must be in kg, g, lbs, or oz. Default is kg');
        MyFatoorah::getWeightRate('sss');
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetDimensionRate()
    {
        $expected = MyFatoorah::getDimensionRate('CM');
        $this->assertEquals(1, $expected);

        $expected2 = MyFatoorah::getDimensionRate('cm');
        $this->assertEquals(1, $expected2);

        $expected3 = MyFatoorah::getDimensionRate('mM');
        $this->assertEquals(0.1, $expected3);
    }

    public function testGetDimensionRateException1()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Dimension units must be in cm, m, mm, in, or yd. Default is cm');
        MyFatoorah::getDimensionRate('');
    }

    public function testGetDimensionRateException2()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Dimension units must be in cm, m, mm, in, or yd. Default is cm');
        MyFatoorah::getDimensionRate('sss');
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testIsSignatureValid()
    {
        $MyFatoorah_Signature1 = 'uRBOogk9ek7Hgsxs/Rt7Nvbu7Vxf+4eI5gwvbtg0NCw=';
        $MyFatoorah_Signature2 = '0YPWuCj1yxScY1gWMUCtilqTL76AAPna8EqedMikhuI=';
        $MyFatoorah_Signature3 = 'XdNvAIV8ZN6CmB2zzapnSemO6lDUpwKk2g/a11GxI8U=';
        $MyFatoorah_Signature4 = '4jsjl0JdWsTLBxqfJ7VxLFLoNhi1EqJaz1c+Z+ri02w=';

        $secret1 = '7wNeL4LfSs/EHVOf0Xzeq1ja+HbPr//2XC2fZO24wA6479AT8o84BmELU2FiRwIpd+rM9W5/egjFuihNSXsHKw==';
        $secret2 = 'Yfsa6MHREzuK+z9VLF3SmoDsdLFEguH970BISF44h5qTSy1jWeH/3FxSVGCEqMPadSmGthmyHP1oz2PFqhoVdg==';
        $secret3 = 'kOYhtuna3DmVilmtlTFI6wNAUX2dH+LSHdMLrmSAjamZKC4B7uSVJmB0+nch4ITGt95ZxoUfQ6Mhbzte7UG7Mw==';
        $secret4 = 'tEtrecTNgRTu+zmde7OZ8pyy62kQTo2sT/tYG0DO2JT626XRKTWeUqwyXsDfE4kMsMSGxjP7KbV0h8pdFG1Pgg==';

        $body1 = '{"EventType":3,"Event":"BalanceTransferred","DateTime":"04072021100512","CountryIsoCode":"KWT","Data":{"DepositReference":"2021000008","DepositedAmount":"1520.664","NumberOfTransactions":"47","DepositFor":"VENDOR","SupplierCode":null}}';
        $body2 = '{"EventType":1,"Event":"BalanceTransferred","DateTime":"04072021100512","CountryIsoCode":"KWT","Data":{"InvoiceId": 34959075,"InvoiceReference": "2021000088","CreatedDate": "24082021144854","CustomerReference": "6124dca568974cf05d7f1e0c","CustomerName": "THE COW","CustomerMobile": "+96590088538","CustomerEmail": null,"TransactionStatus": "SUCCESS","PaymentMethod": "KNET","UserDefinedField": null,"ReferenceId": "123655013425","TrackId": "24-08-2021_32916777","PaymentId": "109202123602930060","AuthorizationId": "659726","InvoiceValueInBaseCurrency": "2.5","BaseCurrency": "KWD","InvoiceValueInDisplayCurreny": "2.5","DisplayCurrency": "KWD","InvoiceValueInPayCurrency": "2.5","PayCurrency": "KWD"}}';
        $body3 = '{"EventType":1,"Event":"TransactionsStatusChanged","DateTime":"13092021114623","CountryIsoCode":"KWT","Data":{"InvoiceId":994285,"InvoiceReference":"2021001240","CreatedDate":"13092021114006","CustomerReference":"139","CustomerName":"رشا سعيد","CustomerMobile":"123456789","CustomerEmail":"rsaeed@myfatoorah.com","TransactionStatus":"FAILED","PaymentMethod":"KNET","UserDefinedField":"139","ReferenceId":"060699428581329564","TrackId":"13-09-2021_813295","PaymentId":"100202125611400734","AuthorizationId":"060699428581329564","InvoiceValueInBaseCurrency":"10.942","BaseCurrency":"KWD","InvoiceValueInDisplayCurreny":"36","DisplayCurrency":"USD","InvoiceValueInPayCurrency":"10.95","PayCurrency":"KWD"}}';
        $body4 = '{"EventType":1,"Event":"TransactionsStatusChanged","DateTime":"14092021012540","CountryIsoCode":"KWT","Data":{"InvoiceId":36301378,"InvoiceReference":"2021520754","CreatedDate":"14092021012424","CustomerReference":"HB8R-3270991","CustomerName":"امل","CustomerMobile":"96599848810","CustomerEmail":"athoob88@hotmail.com","TransactionStatus":"SUCCESS","PaymentMethod":"KNET","UserDefinedField":"ar-61-sale-KWD","ReferenceId":"125720001110","TrackId":"14-09-2021_34159285","PaymentId":"109202125764066719","AuthorizationId":"079903","InvoiceValueInBaseCurrency":"39.25","BaseCurrency":"KWD","InvoiceValueInDisplayCurreny":"39.25","DisplayCurrency":"KWD","InvoiceValueInPayCurrency":"39.25","PayCurrency":"KWD"}}';

        $data1 = json_decode($body1, true);
        $data2 = json_decode($body2, true);
        $data3 = json_decode($body3, true);
        $data4 = json_decode($body4, true);

        $this->assertTrue(MyFatoorah::isSignatureValid($data1['Data'], $secret1, $MyFatoorah_Signature1));
        $this->assertTrue(MyFatoorah::isSignatureValid($data2['Data'], $secret2, $MyFatoorah_Signature2));
        $this->assertTrue(MyFatoorah::isSignatureValid($data3['Data'], $secret3, $MyFatoorah_Signature3));
        $this->assertTrue(MyFatoorah::isSignatureValid($data4['Data'], $secret4, $MyFatoorah_Signature4));

        $this->assertFalse(MyFatoorah::isSignatureValid($data3['Data'], $secret4, $MyFatoorah_Signature4));
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
