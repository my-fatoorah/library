[![Latest Stable Version](https://poser.pugx.org/myfatoorah/library/v)](https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library/tags)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![Build Status](https://scrutinizer-ci.com/g/my-fatoorah/library/badges/build.png?b=main)](https://scrutinizer-ci.com/g/my-fatoorah/library/build-status/main)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/my-fatoorah/library/badges/code-intelligence.svg?b=main)](https://scrutinizer-ci.com/code-intelligence)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/my-fatoorah/library/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/my-fatoorah/library/?branch=main)
[![Total Downloads](https://poser.pugx.org/myfatoorah/library/downloads)](https://packagist.org/packages/myfatoorah/library)

# MyFatoorah - Library
MyFatoorah Payment Gateway PHP library. It is a PHP library to integrate MyFatoorah APIs with your website.

## Install

Via Composer

``` bash
composer require myfatoorah/library
```

## Usage

### Payment Operations

``` php
$config = [
    apiKey => '',
    countryCode => 'KWT',
    isTest => false,
];
$mfObj = new MyFatoorahPayment($config);
$postFields = [
    'NotificationOption' => 'Lnk',
    'InvoiceValue'       => '50',
    'CustomerName'       => 'fname lname',
];

$data = $mfObj->getInvoiceURL($postFields);

$invoiceId   = $data->InvoiceId;
$paymentLink = $data->InvoiceURL;

echo "Click on <a href='$paymentLink' target='_blank'>$paymentLink</a> to pay with invoiceID $invoiceId.";

```

### Shipping Operations

``` php
$config = [
    apiKey => '',
    countryCode => 'KWT',
    isTest => false,
];
$mfObj = new MyFatoorahShipping($config);
$data  = $mfObj->getShippingCountries();

echo 'Country code: ' . $data[0]->CountryCode;
echo 'Country name: ' . $data[0]->CountryName;
```

### General Operations

``` php
$phone = MyFatoorah::getPhone('+2 01234567890');

echo 'Phone code: ' . $phone[0];
echo 'Phone number: ' . $phone[1];

```

## Testing

``` bash
phpunit
```

## Credits

- [MyFatoorah Plugin Team](https://github.com/my-fatoorah)
- [Nermeen Shoman](https://github.com/nermeenshoman)
- [Rasha Saeed](https://github.com/rasha-saeed)

## License

The GPL-3.0-only License.
