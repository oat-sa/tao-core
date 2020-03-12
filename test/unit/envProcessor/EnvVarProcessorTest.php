<?php

namespace oat\tao\test\unit\envProcessor;

use oat\tao\model\envProcessor\EnvNotFoundException;
use oat\tao\model\envProcessor\EnvVarProcessor;
use oat\generis\test\TestCase;

class EnvVarProcessorTest extends TestCase
{
    const TEST_CONST = 'test';


    public function validStrings()
    {
        return [
            ['hello', 'hello'],
            ['true', 'true'],
            ['false', 'false'],
            ['null', 'null'],
            ['1', '1'],
            ['0', '0'],
            ['1.1', '1.1'],
            ['1e1', '1e1'],
        ];
    }

    /**
     * @dataProvider validBools
     */
    public function testGetEnvBool($value, $processed)
    {
        $processor = new EnvVarProcessor();

        $result = $processor->getEnv('bool', 'foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return $value;
        });

        $this->assertSame($processed, $result);
    }

    public function validBools()
    {
        return [
            ['true', true],
            ['false', false],
            ['null', false],
            ['1', true],
            ['0', false],
            ['1.1', true],
            ['1e1', true],
        ];
    }

    /**
     * @dataProvider validInts
     */
    public function testGetEnvInt($value, $processed)
    {
        $processor = new EnvVarProcessor();

        $result = $processor->getEnv('int', 'foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return $value;
        });

        $this->assertSame($processed, $result);
    }

    public function validInts()
    {
        return [
            ['1', 1],
            ['1.1', 1],
            ['1e1', 10],
        ];
    }

    /**
     * @dataProvider invalidInts
     */
    public function testGetEnvIntInvalid($value)
    {
        $this->expectException('RuntimeException', 'Non-numeric env var');
        $processor = new EnvVarProcessor();

        $processor->getEnv('int', 'foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return $value;
        });
    }

    public function invalidInts()
    {
        return [
            ['foo'],
            ['true'],
            ['null'],
        ];
    }

    /**
     * @dataProvider validFloats
     */
    public function testGetEnvFloat($value, $processed)
    {
        $processor = new EnvVarProcessor();

        $result = $processor->getEnv('float', 'foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return $value;
        });

        $this->assertSame($processed, $result);
    }

    public function validFloats()
    {
        return [
            ['1', 1.0],
            ['1.1', 1.1],
            ['1e1', 10.0],
        ];
    }

    /**
     * @dataProvider invalidFloats
     */
    public function testGetEnvFloatInvalid($value)
    {
        $this->setExpectedException('RuntimeException','Non-numeric env var');
        $processor = new EnvVarProcessor();

        $processor->getEnv('float', 'foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return $value;
        });
    }

    public function invalidFloats()
    {
        return [
            ['foo'],
            ['true'],
            ['null'],
        ];
    }

    /**
     * @dataProvider validConsts
     */
    public function testGetEnvConst($value, $processed)
    {
        $processor = new EnvVarProcessor();

        $result = $processor->getEnv('const', 'foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return $value;
        });

        $this->assertSame($processed, $result);
    }

    public function validConsts()
    {
        return [
            ['oat\tao\test\unit\envProcessor\EnvVarProcessorTest::TEST_CONST', self::TEST_CONST],
            ['E_ERROR', E_ERROR],
        ];
    }

    /**
     * @dataProvider invalidConsts
     */
    public function testGetEnvConstInvalid($value)
    {
        $this->setExpectedException('RuntimeException','undefined constant');
        $processor = new EnvVarProcessor();

        $processor->getEnv('const', 'foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return $value;
        });
    }

    public function invalidConsts()
    {
        return [
            ['oat\tao\test\unit\envProcessor\EnvVarProcessorTest::UNDEFINED_CONST'],
            ['UNDEFINED_CONST'],
        ];
    }

    public function testGetEnvBase64()
    {
        $processor = new EnvVarProcessor();

        $result = $processor->getEnv('base64', 'foo', function ($name) {
            $this->assertSame('foo', $name);

            return base64_encode('hello');
        });

        $this->assertSame('hello', $result);
    }

    public function testGetEnvTrim()
    {
        $processor = new EnvVarProcessor();

        $result = $processor->getEnv('trim', 'foo', function ($name) {
            $this->assertSame('foo', $name);

            return " hello\n";
        });

        $this->assertSame('hello', $result);
    }

    /**
     * @dataProvider validJson
     */
    public function testGetEnvJson($value, $processed)
    {
        $processor = new EnvVarProcessor();

        $result = $processor->getEnv('json', 'foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return $value;
        });

        $this->assertSame($processed, $result);
    }

    public function validJson()
    {
        return [
            ['[1]', [1]],
            ['{"key": "value"}', ['key' => 'value']],
            [null, null],
        ];
    }

    public function testGetEnvInvalidJson()
    {
        $this->setExpectedException('RuntimeException', 'Syntax error');
        $processor = new EnvVarProcessor();

        $processor->getEnv('json', 'foo', function ($name) {
            $this->assertSame('foo', $name);

            return 'invalid_json';
        });
    }

    /**
     * @dataProvider otherJsonValues
     */
    public function testGetEnvJsonOther($value)
    {
        $this->setExpectedException('RuntimeException', 'Invalid JSON env var');
        $processor = new EnvVarProcessor();

        $processor->getEnv('json', 'foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return json_encode($value);
        });
    }

    public function otherJsonValues()
    {
        return [
            [1],
            [1.1],
            [true],
            [false],
            ['foo'],
        ];
    }

    public function testGetEnvUnknown()
    {
        $this->setExpectedException('RuntimeException','Unsupported env var prefix');
        $processor = new EnvVarProcessor();

        $processor->getEnv('unknown', 'foo', function ($name) {
            $this->assertSame('foo', $name);

            return 'foo';
        });
    }

    public function testGetEnvKeyInvalidKey()
    {
        $this->setExpectedException('RuntimeException','Invalid env "key:foo": a key specifier should be provided.');
        $processor = new EnvVarProcessor();

        $processor->getEnv('key', 'foo', function ($name) {
            $this->fail('Should not get here');
        });
    }

    /**
     * @dataProvider noArrayValues
     */
    public function testGetEnvKeyNoArrayResult($value)
    {
        $this->setExpectedException('RuntimeException', 'Resolved value of "foo" did not result in an array value.');
        $processor = new EnvVarProcessor();

        $processor->getEnv('key', 'index:foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return $value;
        });
    }

    public function noArrayValues()
    {
        return [
            [null],
            ['string'],
            [1],
            [true],
        ];
    }

    /**
     * @dataProvider invalidArrayValues
     */
    public function testGetEnvKeyArrayKeyNotFound($value)
    {
        $this->expectException(EnvNotFoundException::class, 'Key "index" not found in');
        $processor = new EnvVarProcessor();

        $processor->getEnv('key', 'index:foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return $value;
        });
    }

    public function invalidArrayValues()
    {
        return [
            [[]],
            [['index2' => 'value']],
            [['index', 'index2']],
        ];
    }

    /**
     * @dataProvider arrayValues
     */
    public function testGetEnvKey($value)
    {
        $processor = new EnvVarProcessor();

        $this->assertSame($value['index'], $processor->getEnv('key', 'index:foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return $value;
        }));
    }

    public function arrayValues()
    {
        return [
            [['index' => 'password']],
            [['index' => 'true']],
            [['index' => false]],
            [['index' => '1']],
            [['index' => 1]],
            [['index' => '1.1']],
            [['index' => 1.1]],
            [['index' => []]],
            [['index' => ['val1', 'val2']]],
        ];
    }

    public function testGetEnvKeyChained()
    {
        $processor = new EnvVarProcessor();

        $this->assertSame('password', $processor->getEnv('key', 'index:file:foo', function ($name) {
            $this->assertSame('file:foo', $name);

            return [
                'index' => 'password',
            ];
        }));
    }

    /**
     * @dataProvider validNullables
     */
    public function testGetEnvNullable($value, $processed)
    {
        $processor = new EnvVarProcessor();
        $result = $processor->getEnv('default', ':foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return $value;
        });
        $this->assertSame($processed, $result);
    }

    public function validNullables()
    {
        return [
            ['hello', 'hello'],
            ['null', 'null'],
            ['Null', 'Null'],
            ['NULL', 'NULL'],
         ];
    }

    /**
     * @dataProvider validCsv
     */
    public function testGetEnvCsv($value, $processed)
    {
        $processor = new EnvVarProcessor();

        $result = $processor->getEnv('csv', 'foo', function ($name) use ($value) {
            $this->assertSame('foo', $name);

            return $value;
        });

        $this->assertSame($processed, $result);
    }

    public function validCsv()
    {
        $complex = <<<'CSV'
,"""","foo""","\""",\,foo\
CSV;

        return [
            ['', [null]],
            [',', ['', '']],
            ['1', ['1']],
            ['1,2," 3 "', ['1', '2', ' 3 ']],
            ['\\,\\\\', ['\\', '\\\\']],
            [$complex, \PHP_VERSION_ID >= 70400 ? ['', '"', 'foo"', '\\"', '\\', 'foo\\'] : ['', '"', 'foo"', '\\"",\\,foo\\']],
            [null, null],
        ];
    }
}
