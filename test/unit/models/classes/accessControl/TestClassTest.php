<?php
namespace oat\tao\test\unit\models\classes\accessControl;

use oat\generis\test\TestCase;
use oat\tao\model\accessControl\TestClass;

class TestClassTest extends TestCase
{
    public function testConstructor()
    {
        $this->assertInstanceOf(TestClassTest::class, new TestClass(13));
    }

    public function testAdd()
    {
        $testClass = new TestClass(13);
        $testClass->add(13);

        $this->assertSame('26', $testClass->toString());
    }
}
