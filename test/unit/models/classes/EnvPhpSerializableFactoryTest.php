<?php

namespace oat\tao\test\model;

use oat\tao\model\EnvPhpSerializable;
use oat\tao\model\EnvPhpSerializableFactory;
use PHPUnit\Framework\TestCase;

class EnvPhpSerializableFactoryTest extends TestCase
{
    private const TEST_INDEX = 'TEST_INDEX';

    public function testFactory(): void
    {
        $factory = new EnvPhpSerializableFactory();
        $object = $factory->create(self::TEST_INDEX);
        self::assertInstanceOf(EnvPhpSerializable::class, $object);
        self::assertSame(self::TEST_INDEX, $object->getEnvIndex());
    }
}