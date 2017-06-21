<?php

namespace oat\tao\test\model\metadata\import;

use oat\tao\model\metadata\exception\reader\MetadataReaderNotFoundException;
use oat\tao\model\metadata\reader\KeyReader;

class KeyReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $reader = new KeyReader(array('key' => 'key'));

        $property = new \ReflectionProperty(get_class($reader), 'key');
        $property->setAccessible(true);
        $this->assertEquals('key', $property->getValue($reader));
    }

    /**
     * @dataProvider getValueProvider
     */
    public function testGetValue($key, $data, $expected, $exception)
    {
        $reader = new KeyReader(array('key' => $key));

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
            // Found
            ['key', ['key' => 'expected'], 'expected', false],

            //Not found
            ['key', ['not-expected' => 'value'], '', MetadataReaderNotFoundException::class],
        ];
    }

    /**
     * @dataProvider hasValueProvider
     */
    public function testHasValue($data, $key, $expected)
    {
        $reader = new KeyReader(array('key' => $key));

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