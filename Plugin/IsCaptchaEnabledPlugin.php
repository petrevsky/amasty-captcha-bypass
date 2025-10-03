<?php

declare(strict_types=1);

namespace Magenable\CaptchaBypass\Plugin;

use Amasty\InvisibleCaptcha\Model\Captcha;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Header;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Store\Model\ScopeInterface;

class IsCaptchaEnabledPlugin {
	/**
	 * @var ScopeConfigInterface
	 */
	private ScopeConfigInterface $scopeConfig;

	/**
	 * @var Header
	 */
	private Header $httpHeader;

	/**
	 * @var RemoteAddress
	 */
	private RemoteAddress $remoteAddress;

	/**
	 * @param ScopeConfigInterface $scopeConfig
	 * @param Header $httpHeader
	 * @param RemoteAddress $remoteAddress
	 */
	public function __construct(
		ScopeConfigInterface $scopeConfig,
		Header $httpHeader,
		RemoteAddress $remoteAddress
	) {
		$this->httpHeader    = $httpHeader;
		$this->remoteAddress = $remoteAddress;
		$this->scopeConfig   = $scopeConfig;
	}

	/**
	 * Plugin to bypass Amasty InvisibleCaptcha based on IP or User-Agent whitelist
	 *
	 * @param Captcha $subject
	 * @param bool $result
	 * @return bool
	 */
	public function afterIsNeedToShowCaptcha(
		Captcha $subject,
		bool $result
	): bool {
		// If captcha is already not needed, return early
		if ( ! $result ) {
			return $result;
		}

		// Check if bypass module is enabled
		if ( ! $this->scopeConfig->getValue( 'magenable_captcha_bypass/general/enabled', ScopeInterface::SCOPE_STORE ) ) {
			return $result;
		}

		$ipWhitelisted        = $this->scopeConfig->getValue(
			'magenable_captcha_bypass/general/ip_whitelist',
			ScopeInterface::SCOPE_STORE
		);
		$userAgentWhitelisted = $this->scopeConfig->getValue(
			'magenable_captcha_bypass/general/user_agent_whitelist',
			ScopeInterface::SCOPE_STORE
		);

		// If no whitelist configured, return original result
		if ( ! $ipWhitelisted && ! $userAgentWhitelisted ) {
			return $result;
		}

		// Check IP whitelist
		$checkIp = false;
		if ( $ipWhitelisted ) {
			$ipWhitelistArray = explode( ',', $ipWhitelisted );
			$remoteIp         = $this->remoteAddress->getRemoteAddress();
			foreach ( $ipWhitelistArray as $ipWhite ) {
				if ( $remoteIp === trim( $ipWhite ) ) {
					$checkIp = true;
					break;
				}
			}
		}

		// Check User-Agent whitelist
		$checkUserAgent = false;
		if ( $userAgentWhitelisted ) {
			$userAgent = $this->httpHeader->getHttpUserAgent();
			if ( $userAgent === $userAgentWhitelisted ) {
				$checkUserAgent = true;
			}
		}

		// If either IP or User-Agent matches, bypass captcha
		if ( $checkIp || $checkUserAgent ) {
			return false;
		}

		return $result;
	}
}
