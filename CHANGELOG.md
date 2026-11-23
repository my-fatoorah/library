# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

------------------------------
## [2.2.11] - 2026-11-23
### Added
- Stop the self-update with a `.mf-env` file. Create it next to `autoload.php` holding the word `development`, and the library stops downloading new versions. With no such file - the normal case - the update runs as before.

### Changed
- Make the library safe on PHP 8.5, which is what this release is for: every `curl_close()` call (deprecated since 8.5, without effect since 8.0) now releases the handle instead, in the library and in the loader that ships with the manual install, and the non-canonical `(double)` cast is replaced by `(float)`.
- Supported PHP versions are unchanged: the library still installs on PHP 7.0 and up, and it runs clean on PHP 8.6.
- Relicense from GPL-3.0-only to the MIT License, so any PHP developer can take the code, change it and ship it inside their own product. Its one condition is keeping the copyright notice.
- Report a failed self-update or configuration update to the server error log instead of raising a PHP warning, and name the cause where the library cannot go on.
- Correct the wording of the `Worng event.` exception thrown by `MyFatoorahWebhook`; it now reads `Wrong event.`. A caller matching on the old text must be updated.
- Rename the `$KeyType` parameter of `getPaymentStatus()` to `$keyType`, matching `refund()` and the rest of the library. It only affects callers that pass it as a named argument.

### Fixed
- Harden the two downloads the library makes on its own: its own update from the portal, and the countries configuration behind `mf-config.json`. Neither now overwrites what is installed unless the portal really answered with it; both carry a connect timeout of 3 seconds and a transfer timeout of 6; and a failed download no longer repeats on every page view. Where any of these went wrong, a merchant met a broken page, a slow checkout page, or a damaged configuration.

### Security
- Pass explicit flags to `htmlspecialchars()` in `MyFatoorahHelper::filterInputField()`. The default flags changed in PHP 8.1, so on PHP 8.0 and older single quotes were left unescaped and a filtered value could break out of a single-quoted HTML attribute.

------------------------------
## [2.1.5] - 2026-11-23
### Added
- Add `executePayment()`, correctly spelled.
- Stop the self-update with a `.mf-env` file. Create it next to `autoload.php` holding the word `development`, and the library stops downloading new versions. With no such file - the normal case - the update runs as before.

### Changed
- Make the library safe on PHP 8.5, which is what this release is for: every `curl_close()` call (deprecated since 8.5, without effect since 8.0) now releases the handle instead, in the library and in the loader that ships with the manual install, and the non-canonical `(double)` cast is replaced by `(float)`.
- Supported PHP versions are unchanged: the library still installs on PHP 7.0 and up, and it runs clean on PHP 8.6.
- Relicense from GPL-3.0-only to the MIT License, so any PHP developer can take the code, change it and ship it inside their own product. Its one condition is keeping the copyright notice.
- Report a failed self-update or configuration update to the server error log instead of raising a PHP warning, and name the cause where the library cannot go on.
- Reformat the source to PSR-12. A merchant who patched the library by hand will see a large diff.
- Rename the misspelled `$currncy` parameter of `getPaymentStatus()` to `$currency`, and its `$KeyType` parameter to `$keyType`, matching the rest of the library. Both only affect callers that pass them as named arguments.
- Correct the wording of the `Phone Number lenght` exception thrown by `getPhone()`; it now reads `length`, matching the 2.2 branch. A caller matching on the old text must be updated.

### Deprecated
- `excutePayment()` is kept as a wrapper that forwards to `executePayment()`, so existing callers are unaffected. A subclass that **overrides** `excutePayment()` is no longer reached and should override `executePayment()` instead.

### Fixed
- Judge an invoice expired in the vendor's own time zone instead of Kuwait's. `getErrorData()` read the expiry date the API returns - which is in the vendor's local time - as if it were Kuwait time, so a merchant in Cairo saw an invoice marked `Expired` a full hour before it was, and a merchant in Dubai saw one stay `Pending` an hour after it had expired. Riyadh, Doha and Kuwait City share Kuwait's offset and were never affected. The zone now comes from the country configuration, falling back to Kuwait where the country code is one the library does not know.
- Harden the two downloads the library makes on its own: its own update from the portal, and the countries configuration behind `mf-config.json`. Neither now overwrites what is installed unless the portal really answered with it; both carry a connect timeout of 3 seconds and a transfer timeout of 6; a failed download no longer repeats on every page view; and a missing `mf-config.json` is created again instead of leaving a merchant outside Kuwait on the Kuwait API URL for good. Where any of these went wrong, a merchant met a broken page, a slow checkout page, or the wrong API URL.
- Correct the `Excute Payment` label written to the log file; it now reads `Execute Payment`, matching the 2.2 branch.

------------------------------
## [2.2.10] - 2026-07-12
### Added
- Introduced support for MyFatoorah V3 payments/sessions API

### Changed
- Update autoload.php's self-update routine to:
  1. generate the new V3 class files
  2. add curl connect/read timeouts so a slow update server can't hang the request indefinitely
  3. surface previously silent failures (bad HTTP status/response shape) via trigger_error()

### Fixed
- Replace arrow functions with anonymous functions for compatibility with older PHP versions

------------------------------
## [2.2.9] - 2026-02-12
### Added
- Introduced support for Webhook v2
- Add STC to be displayed in the card section (redirection)

### Changed
- Optimize the "add Gateway To Checkout" function
- Limited embedded Apple Pay / Google Pay to a single gateway
- Apply the regex expression for the customer name, invoice item names, item product names, and item product description

### Fixed
- Fix displaying of Arabic letters in the log

------------------------------
## [2.1.4] - 2024-12-02
### Changed
- Remove the mf-config.json file to ensure a fresh download during installation.

------------------------------
## [2.2.8] - 2024-09-06
### Fixed
- Fix the display of the checkout price.

------------------------------
## [2.2.7] - 2024-03-31
### Fixed
- Fix the display of the checkout price.
- Trim the config JSON file to remove any hidden chars.

------------------------------
## [2.2.6] - 2024-02-11
### Added
- Add the getPaymentStatusLink static function
- Add the getOneCurrencyRate static function
- Add the getTranslatedCurrency static function to translate the currency

### Changed
- Change the config array to accept the vcCode and countryCode

------------------------------
## [2.2.5] - 2023-12-05
### Changed
- Some changes to the library to be compatible with other API rather than plugins

------------------------------
## [2.2.4] - 2023-06-26
### Added
- Add google pay gateway

------------------------------
## [2.2.3] - 2023-05-24
### Fixed
- Fix display price value in calc Gateway Data

------------------------------
## [2.2.2] - 2023-05-02
### Added
- Add makeRefund API function
- Add more test files

### Fixed
- Fix autoload file

------------------------------
## [2.1.3] - 2023-05-24
### Changed
- Modify refund

### Fixed
- Fix display price value in calc Gateway Data

------------------------------
## [2.1.2] - 2023-05-02
### Added
- Add notification options as param in send payment function

### Changed
- Modify refund

### Fixed
- Fix apple pay striptags

------------------------------
## [2.2.1] - 2023-03-30
### Added
- Add notification options as param in send payment function

### Changed
- Modify refund

### Fixed
- Fix apple pay striptags

------------------------------
## [2.2.0] - 2023-01-04
### Changed
- Restructure the library files

------------------------------
## [2.1.1] - 2023-01-04
### Fixed
- Fix many requests in the MyFatoorah autoloader

------------------------------
## [2.1.0] - 2022-09-18
### Added
- Added Apple Pay Embedded Button

### Changed
- Optimization in some functions

### Fixed
- Fix compatibility with PHP 8.1

------------------------------
## [2.0.0] - 2022-07-04
### Added
- The first version of the library

------------------------------
[2.2.11]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.2.11
[2.2.10]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.2.10
[2.2.9]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.2.9
[2.2.8]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.2.8
[2.2.7]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.2.7
[2.2.6]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.2.6
[2.2.5]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.2.5
[2.2.4]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.2.4
[2.2.3]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.2.3
[2.2.2]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.2.2
[2.2.1]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.2.1
[2.2.0]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.2.0
[2.1.5]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.1.5
[2.1.4]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.1.4
[2.1.3]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.1.3
[2.1.2]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.1.2
[2.1.1]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.1.1
[2.1.0]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.1.0
[2.0.0]: https://dev.azure.com/myfatoorahsc/Public-Repo/_git/Library?version=GT2.0.0
