<?php

namespace oat\tao\test\unit\helpers;

use oat\tao\helpers\Layout;
use PHPUnit\Framework\TestCase;

class LayoutTest extends TestCase
{
    protected function setUp(): void
    {
        $this->setEnv('NODE_ENV', 'production');

        Layout::setTemplate(TemplateMock::class);
    }

    protected function tearDown(): void
    {
        TemplateMock::resetCalls();
    }

    public function testPrintAnalyticsCodeWithGaTag(): void
    {
        $this->setEnv('GA_TAG', 'dummy-ga-tag');

        Layout::printAnalyticsCode();

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
            TemplateMock::getCalls()
        );
    }

    public function testPrintAnalyticsCodeWithoutGaTag(): void
    {
        $this->setEnv('GA_TAG', '');

        Layout::printAnalyticsCode();

        self::assertSame(
            [],
            TemplateMock::getCalls()
        );
    }

    protected function setEnv($key, $value): void
    {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}
