<?php

namespace oat\tao\test\unit\helpers;

use oat\tao\helpers\Layout;
use oat\tao\helpers\Template;
use PHPUnit\Framework\TestCase;

class LayoutTest extends TestCase
{
    private Template $templateMock;

    protected function setUp(): void
    {
        $this->setEnv('NODE_ENV', 'production');

        $this->templateMock = new TemplateMock();

        Layout::setTemplate($this->templateMock);
    }

    protected function tearDown(): void
    {
        $this->templateMock->resetCalls();
    }

    public function testGetAnalyticsCodeWithGaTag(): void
    {
        $this->setEnv('GA_TAG', 'dummy-ga-tag');

        Layout::getAnalyticsCode();

        self::assertSame(
            [
                [
                    'oat\tao\test\unit\helpers\TemplateMock::inc' => [
                        'blocks/analytics.tpl',
                        'tao',
                        [
                            'gaTag' => 'dummy-ga-tag',
                            'environment' => 'Production'
                        ]
                    ]
                ]
            ],
            $this->templateMock->getCalls()
        );
    }

    public function testGetAnalyticsCodeWithoutGaTag(): void
    {
        $this->setEnv('GA_TAG', '');

        Layout::getAnalyticsCode();

        self::assertSame(
            [],
            $this->templateMock->getCalls()
        );
    }

    private function setEnv($key, $value)
    {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}
