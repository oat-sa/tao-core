<?php

namespace oat\tao\test\unit\helpers;

use oat\generis\test\TestCase;
use oat\tao\helpers\LocaleFilesHelper;

class LocaleFilesHelperTest extends TestCase
{
    private string $postfix;

    private string $pattern;

    protected function setUp(): void
    {
        $this->postfix = "-S";
        $this->pattern = sprintf('/%s$/', $this->postfix);
    }

    public function testIsPostfixNotApplied(): void
    {
        $localeDir = '/locales/en-US';
        $this->assertFalse(LocaleFilesHelper::isPostfixApplied($localeDir, $this->pattern));
    }

    public function testIsPostfixApplied(): void
    {
        $localeDir = '/locales/en-US-S';
        $this->assertTrue(LocaleFilesHelper::isPostfixApplied($localeDir, $this->pattern));
    }
}
