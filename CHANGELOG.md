# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2024-10-03

### Added

-   Amasty InvisibleCaptcha compatibility
-   Plugin for `Amasty\InvisibleCaptcha\Model\Captcha::isNeedToShowCaptcha()`
-   Comprehensive integration guide (AMASTY_INTEGRATION.md)
-   Changes summary documentation
-   .gitignore file

### Changed

-   Package name from `magenable/module-captcha-bypass` to `petrevsky/amasty-captcha-bypass`
-   Removed `magenable/module-base` dependency for standalone installation
-   Updated admin configuration location to **Advanced** → **Captcha Bypass**
-   Plugin target from Magento native ReCaptcha to Amasty's Captcha model
-   Module dependency from `Magento_ReCaptchaUi` to `Amasty_InvisibleCaptcha`
-   Enhanced README with testing examples and troubleshooting
-   Updated composer.json keywords and author information

### Removed

-   Dependency on `Magenable_Base` module
-   Dependency on `Magento_ReCaptchaUi` module

## [1.0.8] - Previous Release

### Features

-   IP address whitelist bypass for Magento native reCaptcha
-   User-Agent whitelist bypass for Magento native reCaptcha
-   Admin configuration panel
