<?php

declare(strict_types=1);

namespace oat\tao\model\lockDownBrowser\Service;

use Context;
use oat\tao\model\lockDownBrowser\Entity\LockDownBrowser;

final class HttpRequestLockDownBrowserDetector implements LockDownBrowserDetectorInterface
{
    public const REQUEST_PARAM_CLIENT_KIOSK_API_OK = 'clientKioskApiOk';

    public function detect($clientKioskApiOk = null): string
    {
        if ($this->isClientKioskApiOk($clientKioskApiOk)) {
            return LockDownBrowser::PROVIDER_KIOSKED;
        }

        if ($this->isUserAgentKiosked()) {
            return LockDownBrowser::PROVIDER_KIOSKED;
        }

        return LockDownBrowser::PROVIDER_NONE;
    }

    private function isUserAgentKiosked(): bool
    {
        $userAgent = $this->getUserAgent();

        if ($userAgent === '') {
            return false;
        }

        return (bool)preg_match('/kiosk(ed)?/i', $userAgent);
    }

    private function getUserAgent(): string
    {
        try {
            $request = Context::getInstance()->getRequest();

            if ($request->hasHeader('User-Agent')) {
                return (string)$request->getHeader('User-Agent');
            }
        } catch (\Throwable $exception) {
            // fall through to $_SERVER
        }

        return (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    }

    private function isClientKioskApiOk($explicit = null): bool
    {
        if ($explicit !== null) {
            return filter_var($explicit, FILTER_VALIDATE_BOOLEAN);
        }

        $fromRequest = $this->readClientKioskApiOkFromContextRequest();

        if ($fromRequest !== null) {
            return filter_var($fromRequest, FILTER_VALIDATE_BOOLEAN);
        }

        if (!isset($_REQUEST[self::REQUEST_PARAM_CLIENT_KIOSK_API_OK])) {
            return false;
        }

        return filter_var($_REQUEST[self::REQUEST_PARAM_CLIENT_KIOSK_API_OK], FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return mixed|null
     */
    private function readClientKioskApiOkFromContextRequest()
    {
        try {
            $request = Context::getInstance()->getRequest();

            if ($request->hasParameter(self::REQUEST_PARAM_CLIENT_KIOSK_API_OK)) {
                return $request->getParameter(self::REQUEST_PARAM_CLIENT_KIOSK_API_OK);
            }
        } catch (\Throwable $exception) {
            return null;
        }

        return null;
    }
}
