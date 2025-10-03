# Captcha Bypass for Amasty InvisibleCaptcha

Bypass Google reCAPTCHA for defined IP addresses or User-Agent strings. Useful for automated testing with Selenium, Cypress, or other testing tools.

**Compatible with Amasty InvisibleCaptcha extension.**

## Requirements

-   Magento 2.4+
-   Amasty InvisibleCaptcha extension must be installed and enabled

## Installation

### Via Composer (Recommended)

```bash
composer require petrevsky/amasty-captcha-bypass
bin/magento module:enable Magenable_CaptchaBypass
bin/magento setup:upgrade
bin/magento cache:clean
```

### Manual Installation

1. Copy the module to `app/code/Magenable/CaptchaBypass`
2. Run the following commands:

```bash
bin/magento module:enable Magenable_CaptchaBypass
bin/magento setup:upgrade
bin/magento cache:clean
```

## Upgrade

```bash
composer update petrevsky/amasty-captcha-bypass
bin/magento setup:upgrade
bin/magento cache:clean
```

## Configuration

1. Go to **Stores** → **Configuration** → **Advanced** → **Captcha Bypass**
2. Set **Enabled** to **Yes**
3. Configure bypass rules:
    - **Whitelisted IP addresses**: Comma-separated IPs (e.g., `127.0.0.1,192.168.1.100`)
    - **Whitelisted User-Agent**: Exact User-Agent string (e.g., `Selenium WebDriver` or `Cypress/12.0`)
4. Save configuration and clear cache

If one of the conditions is met, the reCAPTCHA will be bypassed.

## How It Works

This module intercepts Amasty InvisibleCaptcha's `isNeedToShowCaptcha()` method. When enabled:

-   If the visitor's IP address matches any in the whitelist, captcha will be bypassed
-   If the visitor's User-Agent matches the configured value, captcha will be bypassed
-   The module respects Amasty's native IP whitelist and adds additional bypass capabilities

## Testing Examples

### Selenium WebDriver

```python
from selenium import webdriver

options = webdriver.ChromeOptions()
options.add_argument("user-agent=Selenium WebDriver")
driver = webdriver.Chrome(options=options)
```

### Cypress

```javascript
cy.visit(url, {
    headers: {
        "User-Agent": "Cypress/12.0",
    },
});
```

### cURL

```bash
curl -X POST https://your-store.com/customer/account/loginPost \
  -H "User-Agent: TestBot/1.0" \
  -d "login[username]=test@example.com&login[password]=Test123!"
```

## Compatibility

-   ✅ Amasty InvisibleCaptcha v2.5.1+
-   ✅ Magento 2.4.5, 2.4.6, 2.4.7
-   ✅ reCAPTCHA v2 and v3

## Security Notes

⚠️ **Important**: This module bypasses security controls.

**Recommended use cases:**

-   Development environments
-   Testing/QA environments
-   Automated testing (CI/CD pipelines)

**NOT recommended for:**

-   Production environments
-   Public-facing stores

If using in production, ensure:

-   Only trusted IPs/User-Agents are whitelisted
-   Alternative security measures are in place
-   Regular security audits are performed

## Troubleshooting

### Module not working?

1. Verify module is enabled:

    ```bash
    bin/magento module:status Magenable_CaptchaBypass
    ```

2. Check Amasty is installed:

    ```bash
    bin/magento module:status Amasty_InvisibleCaptcha
    ```

3. Clear all caches:

    ```bash
    bin/magento cache:flush
    ```

4. Recompile (if in production mode):
    ```bash
    bin/magento setup:di:compile
    ```

### Still showing captcha?

-   Verify IP format is correct (no spaces after commas)
-   Check User-Agent matches exactly (case-sensitive)
-   Ensure module is enabled in **Stores** → **Configuration** → **Advanced** → **Captcha Bypass**
-   Check Magento logs: `var/log/system.log`

## Documentation

-   [Integration Guide](AMASTY_INTEGRATION.md) - Technical details and testing
-   [Changes Summary](CHANGES_SUMMARY.md) - What was modified for Amasty compatibility
-   [Changelog](CHANGELOG.md) - Version history

## Support

For issues or questions:

1. Check [AMASTY_INTEGRATION.md](AMASTY_INTEGRATION.md) troubleshooting section
2. Review Magento logs
3. Open an issue on GitHub

## License

MIT License - see [LICENSE](LICENSE) file for details

## Credits

Original module by [Magenable](https://magenable.com.au)  
Amasty compatibility by Petrevsky
