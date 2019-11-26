<?php

declare(strict_types=1);

namespace oat\tao\test\unit\model\metadata\import;

use oat\generis\test\TestCase;
use oat\tao\model\metadata\exception\reader\MetadataReaderNotFoundException;
use oat\tao\model\metadata\reader\KeyReader;

class KeyReaderTest extends TestCase
{
    public function testConstruct(): void
    {
        $reader = new KeyReader(['key' => 'key']);

        $property = new \ReflectionProperty(get_class($reader), 'key');
        $property->setAccessible(true);
        $this->assertSame('key', $property->getValue($reader));
    }

    public function testGetValue(): void
    {
        $reader = new KeyReader(['key' => 'key']);

        $value = $reader->getValue(['key' => 'expected']);
        $this->assertSame('expected', $value);
    }

    public function testGetValueException(): void
    {
        $this->expectException(MetadataReaderNotFoundException::class);
        $reader = new KeyReader(['key' => 'key']);
        $reader->getValue(['not-expected' => 'value']);
    }

    /**
     * @dataProvider hasValueProvider
     */
    public function testHasValue($data, $key, $expected): void
    {
        $reader = new KeyReader(['key' => $key]);

        $method = new \ReflectionMethod(get_class($reader), 'hasValue');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invokeArgs($reader, [$data, $key]));
    }

    public function hasValueProvider()
    {
        return [
            //Found
            [['polop' => 'fixture'], 'polop', true],

            // Not found
            [['polop' => 'fixture'], 'otherKey', false],

            // Key is not a string
            [['polop' => 'fixture'], ['array'], false],

        ];
    }
}
