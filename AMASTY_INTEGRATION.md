# Amasty InvisibleCaptcha Integration Guide

## Overview

This document explains the modifications made to enable compatibility with Amasty InvisibleCaptcha extension, making the module completely standalone and installable via Composer.

## Changes Made

### 1. Plugin Class (`Plugin/IsCaptchaEnabledPlugin.php`)

**Changed:**

-   **Use statement**: Changed from `Magento\ReCaptchaUi\Model\IsCaptchaEnabled` to `Amasty\InvisibleCaptcha\Model\Captcha`
-   **Method name**: Changed from `afterIsCaptchaEnabledFor()` to `afterIsNeedToShowCaptcha()`
-   **Type hint**: Changed `IsCaptchaEnabled $subject` to `Captcha $subject`
-   **Return type**: Added explicit return type `: bool`

**Logic improvements:**

-   Added early return if captcha is already not needed (`if (!$result)`)
-   Better code comments explaining the flow
-   Renamed variable for clarity: `$ipWhitelisted` → `$ipWhitelistArray` in explode operation

### 2. Dependency Injection (`etc/di.xml`)

**Changed:**

```xml
<!-- FROM -->
<type name="Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface">
    <plugin name="magenable_captcha_bypass_plugin_is_captcha_enabled"
            type="Magenable\CaptchaBypass\Plugin\IsCaptchaEnabledPlugin" sortOrder="1" disabled="false"/>
</type>

<!-- TO -->
<type name="Amasty\InvisibleCaptcha\Model\Captcha">
    <plugin name="magenable_captcha_bypass_plugin_amasty_captcha"
            type="Magenable\CaptchaBypass\Plugin\IsCaptchaEnabledPlugin" sortOrder="100" disabled="false"/>
</type>
```

**Note:** `sortOrder="100"` ensures this plugin runs after Amasty's internal checks.

### 3. Module Dependencies (`etc/module.xml`)

**Changed:**

```xml
<!-- FROM -->
<sequence>
    <module name="Magenable_Base"/>
    <module name="Magento_ReCaptchaUi"/>
</sequence>

<!-- TO -->
<sequence>
    <module name="Amasty_InvisibleCaptcha"/>
</sequence>
```

**Removed** `Magenable_Base` dependency to make the module standalone.

### 4. Admin Configuration (`etc/adminhtml/system.xml`)

**Changed:**

```xml
<!-- FROM -->
<tab>magenable</tab>

<!-- TO -->
<tab>advanced</tab>
```

The configuration now appears under **Stores** → **Configuration** → **Advanced** → **Captcha Bypass** instead of requiring a custom Magenable tab.

### 5. Composer (`composer.json`)

**Changed:**

-   Package name: `magenable/module-captcha-bypass` → `petrevsky/amasty-captcha-bypass`
-   Removed requirement: `magenable/module-base`
-   Added requirement: `amasty/module-invisible-captcha`
-   Updated version: `1.0.8` → `1.1.0`
-   Updated author information
-   Enhanced keywords for better discoverability
-   Added `minimum-stability: stable`

## How It Works

### Amasty's Validation Flow

1. User submits a form (login, register, checkout, etc.)
2. `FrontControllerInterface` is intercepted by Amasty's `ValidateCaptcha` plugin
3. Amasty calls `Captcha::isNeedToShowCaptcha()` to determine if validation is needed
4. If `true`, Amasty validates the captcha token
5. If `false`, captcha validation is skipped

### Our Plugin's Role

Our plugin intercepts step 3 (`isNeedToShowCaptcha()`) using an `after` plugin:

```
Amasty checks → Our plugin checks → Final decision
     ↓               ↓                    ↓
  true/false    IP/UA match?        true/false
                     ↓
              Yes → return false (bypass)
              No → return original result
```

## Installation via Composer

### Method 1: From Packagist (after publishing)

```bash
composer require petrevsky/amasty-captcha-bypass
bin/magento module:enable Magenable_CaptchaBypass
bin/magento setup:upgrade
bin/magento cache:clean
```

### Method 2: Direct from GitHub

Add to your project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/petrevsky/amasty-captcha-bypass"
        }
    ]
}
```

Then run:

```bash
composer require petrevsky/amasty-captcha-bypass
bin/magento module:enable Magenable_CaptchaBypass
bin/magento setup:upgrade
bin/magento cache:clean
```

## Configuration

### Admin Panel Setup

1. **Enable the Module:**

    - Go to `Stores → Configuration → Advanced → Captcha Bypass`
    - Set `Enabled` to `Yes`

2. **Configure IP Whitelist:**

    - Add comma-separated IPs: `127.0.0.1,192.168.1.100`
    - These IPs will bypass Amasty captcha

3. **Configure User-Agent Whitelist:**
    - Add exact User-Agent string: `Selenium WebDriver`
    - Requests with this User-Agent will bypass captcha

## Testing

### Test IP Bypass

```bash
# From whitelisted IP
curl -X POST https://your-store.com/customer/account/createpost \
  -d "firstname=Test&lastname=User&email=test@example.com&password=Test123!"
```

### Test User-Agent Bypass

```bash
# With whitelisted User-Agent
curl -X POST https://your-store.com/customer/account/createpost \
  -H "User-Agent: Selenium WebDriver" \
  -d "firstname=Test&lastname=User&email=test@example.com&password=Test123!"
```

### Selenium Example

```python
from selenium import webdriver

options = webdriver.ChromeOptions()
options.add_argument("user-agent=Selenium WebDriver")
driver = webdriver.Chrome(options=options)
driver.get("https://your-store.com/customer/account/create")
```

### Verify Bypass is Working

Check Magento logs:

```bash
tail -f var/log/system.log | grep -i captcha
```

## Troubleshooting

### Module Not Working

1. **Check module is enabled:**

    ```bash
    bin/magento module:status Magenable_CaptchaBypass
    ```

2. **Check Amasty is installed:**

    ```bash
    bin/magento module:status Amasty_InvisibleCaptcha
    ```

3. **Clear cache:**

    ```bash
    bin/magento cache:clean
    bin/magento cache:flush
    ```

4. **Recompile (if in production mode):**
    ```bash
    bin/magento setup:di:compile
    ```

### Plugin Not Firing

Check if the plugin is registered:

```bash
bin/magento setup:di:compile
# Check generated/metadata/global.php for your plugin
```

### Still Showing Captcha

1. Verify your IP is correctly whitelisted (no spaces after commas)
2. Check User-Agent matches exactly (case-sensitive)
3. Ensure the bypass module is enabled in admin config: **Advanced** → **Captcha Bypass**
4. Check if Amasty has its own IP whitelist configured (they work together)
5. Clear configuration cache: `bin/magento cache:clean config`

### Configuration Not Visible

If you don't see **Captcha Bypass** under **Advanced** in admin:

1. Clear config cache: `bin/magento cache:clean config`
2. Logout and login to admin
3. Check module is enabled: `bin/magento module:status`

## Compatibility Notes

### Works With:

-   ✅ Amasty InvisibleCaptcha v2.5.1+
-   ✅ Magento 2.4.5+
-   ✅ Both reCAPTCHA v2 and v3

### Respects:

-   ✅ Amasty's native IP whitelist
-   ✅ Amasty's "Enable for Guests Only" setting
-   ✅ Amasty's customer group restrictions

### Extends:

-   ✅ Adds User-Agent bypass capability
-   ✅ Provides independent IP whitelist management
-   ✅ No external dependencies (except Amasty itself)

## Security Considerations

⚠️ **Important:** This module bypasses security controls. Use only for:

-   Development environments
-   Testing/QA environments
-   Automated testing (Selenium, Cypress, etc.)
-   CI/CD pipelines

❌ **Do NOT use in production** unless you:

-   Fully understand the security implications
-   Have alternative security measures in place
-   Whitelist only trusted IPs/User-Agents
-   Regularly audit access

## Publishing to Packagist

### Steps to Publish

1. **Push to GitHub:**

    ```bash
    git init
    git add .
    git commit -m "Initial commit - Amasty InvisibleCaptcha compatibility"
    git remote add origin https://github.com/petrevsky/amasty-captcha-bypass.git
    git push -u origin main
    ```

2. **Create a Release:**

    - Go to GitHub → Releases → Create new release
    - Tag: `v1.1.0`
    - Title: `v1.1.0 - Amasty InvisibleCaptcha Support`
    - Description: See CHANGELOG.md

3. **Submit to Packagist:**
    - Go to https://packagist.org
    - Click "Submit"
    - Enter: `https://github.com/petrevsky/amasty-captcha-bypass`
    - Packagist will auto-sync with your GitHub releases

## Support

For issues or questions:

1. Check this integration guide
2. Check Magento logs: `var/log/system.log` and `var/log/exception.log`
3. Verify Amasty InvisibleCaptcha is properly configured and working
4. Open an issue on GitHub

## Version History

-   **v1.1.0** - Added Amasty InvisibleCaptcha compatibility, removed external dependencies
-   **v1.0.8** - Original version (Magento native reCAPTCHA only, required Magenable_Base)
