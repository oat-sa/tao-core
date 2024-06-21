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
        $this->pattern = '/' . $this->postfix . '$/';
    }

    public function testIsPostfixNotApplied(): void
    {
        $localeDir = '/locales/en-US';
        $isPostfixApplied = false;
        $this->assertSame($isPostfixApplied, LocaleFilesHelper::isPostfixApplied($localeDir, $this->pattern));
    }

    public function testIsPostfixApplied(): void
    {
        $localeDir = '/locales/en-US-S';
        $isPostfixApplied = true;
        $this->assertSame($isPostfixApplied, LocaleFilesHelper::isPostfixApplied($localeDir, $this->pattern));
    }
}
