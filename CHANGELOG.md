# Release Notes for TaxJar for Craft Commerce

## 3.1.0 - 2026-04-29

### Added
- The plugin now has a settings page in the control panel.
- The API Key and Sandbox Mode settings now support environment variables.
- Added a `Categories` service (`Plugin::getInstance()->getCategories()`) with a `sync()` method for syncing TaxJar categories into Craft Commerce.
- Added `commerce-taxjar/categories` and `commerce-taxjar/categories/sync` console commands for listing and syncing TaxJar tax categories.

### Fixed
- Fixed a 400 error from the TaxJar API when recalculating an order with no line items. ([#34](https://github.com/craftcms/commerce-taxjar/issues/34))

### Changed
- The plugin's main class has been renamed from `craft\commerce\taxjar\TaxJar` to `craft\commerce\taxjar\Plugin`.
- Category sync now updates existing categories in addition to creating new ones.

## 3.0.0 - 2024-03-20

- TaxJar now requires Craft Commerce 5 or later.

## 2.1.0 - 2024-03-20

### Added
- Added `craft\commerce\taxjar\events\ModifyRequestEvent`.
- Provided a more precise store location address to the Tax Jar API. ([#13](https://github.com/craftcms/commerce-taxjar/pull/13))
- Fixed a bug where cached tax rates were not being invalidated when line item tax category changed. ([#11](https://github.com/craftcms/commerce-taxjar/issues/11))

## 2.0.0 - 2022-05-04

### Added
- Added Commerce 4 compatibility.

## 1.0.1 - 2021-02-25

### Fixed
- Fixed a PHP error that would occur on Craft 3.5. ([#7](https://github.com/craftcms/commerce-taxjar/issues/7))
- Fixed a bug where long category descriptions would cause Tax Jar category sync to fail. ([#5](https://github.com/craftcms/commerce-taxjar/issues/5))

## 1.0.0 - 2020-04-02

### Added
- Initial release.
