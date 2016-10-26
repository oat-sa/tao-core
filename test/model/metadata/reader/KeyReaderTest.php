<?php

namespace oat\tao\test\model\metadata\import;

use oat\tao\model\metadata\exception\reader\MetadataReaderNotFoundException;
use oat\tao\model\metadata\reader\KeyReader;
use oat\tao\test\TaoPhpUnitTestRunner;

class KeyReaderTest extends TaoPhpUnitTestRunner
{
    public function testConstruct()
    {
        $reader = new KeyReader('alias', 'key');

        $property = new \ReflectionProperty(get_class($reader), 'key');
        $property->setAccessible(true);
        $this->assertEquals('key', $property->getValue($reader));

        $property = new \ReflectionProperty(get_class($reader), 'alias');
        $property->setAccessible(true);
        $this->assertEquals('alias', $property->getValue($reader));
    }

    /**
     * @dataProvider getValueProvider
     */
    public function testGetValue($alias, $key, $data, $expected, $exception)
    {
        $reader = new KeyReader($alias, $key);

        if ($exception) {
            $this->setExpectedException($exception);
            $reader->getValue($data);
        } else {
            $value = $reader->getValue($data);
            $this->assertEquals($expected, $value);
        }
    }

    public function getValueProvider()
    {
        return [
            // Found by alias
            ['alias', 'key', ['alias' => 'expected'], 'expected', false],
            ['alias', 'key', ['alias' => 'expected', 'key' => 'otherValue'], 'expected', false],

            //Found by key
            ['alias', 'key', ['key' => 'expected'], 'expected', false],

            //Not found
            ['alias', 'key', ['not-expected' => 'value'], '', MetadataReaderNotFoundException::class],
        ];
    }

    /**
     * @dataProvider hasValueProvider
     */
    public function testHasValue($data, $key, $expected)
    {
        $reader = new KeyReader('alias', 'key');

        $method = new \ReflectionMethod(get_class($reader), 'hasValue');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invokeArgs($reader, [$data, $key]));
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