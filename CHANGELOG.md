# Release Notes for Craft Commerce

## 2.1.0 - Unreleased

### Added
- Added Craft CMS 5 and Craft Commerce 5 compatibility.
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
