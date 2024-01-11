<?php

namespace oat\tao\test\unit\helpers;

use oat\tao\helpers\Template;

/**
 * Class responsible for mocking Template class - in order to make static methods testable.
 * It does not call actual methods, but collects called mocked methods and it's arguments instead.
 * Currently supported mocked static methods:
 *  - 'inc'
 */
class TemplateMock extends Template
{
    private static array $calls = [];

    public static function getCalls(): array
    {
        return self::$calls;
    }

    public static function resetCalls(): void
    {
        self::$calls = [];
    }

    public static function inc($path, $extensionId = null, $data = [])
    {
        self::$calls[] = [
            __METHOD__ => func_get_args()
        ];
    }
}
