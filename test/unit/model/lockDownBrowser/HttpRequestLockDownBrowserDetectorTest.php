<?php

declare(strict_types=1);

namespace oat\tao\test\unit\model\lockDownBrowser;

use oat\tao\model\lockDownBrowser\Entity\LockDownBrowser;
use oat\tao\model\lockDownBrowser\Service\HttpRequestLockDownBrowserDetector;
use PHPUnit\Framework\TestCase;

class HttpRequestLockDownBrowserDetectorTest extends TestCase
{
    private array $serverBackup;
    private array $requestBackup;

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        $this->requestBackup = $_REQUEST;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        $_REQUEST = $this->requestBackup;
    }

    public function testDetectReturnsKioskedWhenUaAndClientOk(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla Kiosked/1.0';
        $_REQUEST[HttpRequestLockDownBrowserDetector::REQUEST_PARAM_CLIENT_KIOSK_API_OK] = '1';

        $detector = new HttpRequestLockDownBrowserDetector();

        $this->assertSame(LockDownBrowser::PROVIDER_KIOSKED, $detector->detect());
    }

    public function testDetectReturnsNoneWhenClientNotOk(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla Kiosked/1.0';
        $_REQUEST[HttpRequestLockDownBrowserDetector::REQUEST_PARAM_CLIENT_KIOSK_API_OK] = '0';

        $detector = new HttpRequestLockDownBrowserDetector();

        $this->assertSame(LockDownBrowser::PROVIDER_NONE, $detector->detect());
    }

    public function testDetectReturnsKioskedWhenClientOkInRequestWithoutKioskUa(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Chrome';
        $_REQUEST[HttpRequestLockDownBrowserDetector::REQUEST_PARAM_CLIENT_KIOSK_API_OK] = '1';

        $detector = new HttpRequestLockDownBrowserDetector();

        $this->assertSame(LockDownBrowser::PROVIDER_KIOSKED, $detector->detect());
    }

    public function testDetectReturnsKioskedWhenExplicitClientOkWithoutKioskUa(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Chrome';
        $_REQUEST[HttpRequestLockDownBrowserDetector::REQUEST_PARAM_CLIENT_KIOSK_API_OK] = '0';

        $detector = new HttpRequestLockDownBrowserDetector();

        $this->assertSame(LockDownBrowser::PROVIDER_KIOSKED, $detector->detect('1'));
    }
}
