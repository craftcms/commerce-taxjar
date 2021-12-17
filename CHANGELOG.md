# Release Notes for Craft Commerce

## 1.0.4 - 2021-12-17

### Added
- Added `craft\commerce\taxjar\events\SetAddressForTaxEvent`
- Added `craft\commerce\taxjar\adjusters\TaxJar::SET_ADDRESS_FOR_TAX_EVENT`
- Added `craft\commerce\taxjar\services\Api::TYPE_FROM`
- Added `craft\commerce\taxjar\services\Api::TYPE_TO`
- Added `craft\commerce\taxjar\services\Api::_getAddressParams()`

## 1.0.3 - 2021-10-07

### Changed
- Changed how deductions are handled in refunds. Deduction amounts no longer affect sales tax returned to the customer or data sent to TaxJar.

### Fixed
- Fixed a bug that could occur when order was fully paid with gift voucher.

## 1.0.2 - 2021-07-21

### Added
- Added ability to commit transactions.
- Added ability to create refunds.

## 1.0.1 - 2021-02-25

### Fixed
- Fixed a PHP error that would occur on Craft 3.5. ([#7](https://github.com/craftcms/commerce-taxjar/issues/7))
- Fixed a bug where long category descriptions would cause Tax Jar category sync to fail. ([#5](https://github.com/craftcms/commerce-taxjar/issues/5))

## 1.0.0 - 2020-04-02

### Added
- Initial release.