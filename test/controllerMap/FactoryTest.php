<?php
/**
 * Created by PhpStorm.
 * User: ksasim
 * Date: 01.07.15
 * Time: 12:13
 */

use oat\tao\model\controllerMap\Factory;
use oat\tao\model\controllerMap\ControllerDescription;
use oat\tao\model\controllerMap\ActionDescription;
use oat\tao\test\TaoPhpUnitTestRunner;
include_once dirname(__FILE__) . '/../../includes/raw_start.php';

/* stubs for controller class name validation */
class FakeStandaloneController {

}

abstract class FakeAbstractController extends Module {

}

class FakeValidController extends Module {

}

class FactoryTest extends TaoPhpUnitTestRunner {

    /** @var  Factory */
    protected $factory;

    protected function setUp()
    {
        parent::setUp();

        $this->factory = new Factory();
    }

    /**
     * Provides data for {@link self::testGetControllerDescription}: controller name (non-namespaced, namespaced)
     *
     * @return array[] the data
     */
    public function controllerNameProvider()
    {
        return array(
            array('tao_actions_Main'),
            array('oat\\taoQtiItem\\controller\\Parser'),
        );
    }

    /**
     * Provides valid data for {@link self::testGetActionDescription}:
     *  - controller name (non-namespaced, namespaced)
     *  - action name
     *  - description
     *  - required rights
     *
     * @return array[] the data
     */
    public function controllerActionNameProvider()
    {
        return array(
            array(
                'tao_actions_Main',
                'index',
                'The main action, load the layout',
                array()
            ),
            array(
                'taoItems_actions_ItemExport',
                'index',
                'overwrite the parent index to add the requiresRight for Items only',
                array(
                    'id' => 'READ'
                )),
            array(
                'oat\\taoQtiItem\\controller\\Parser',
                'getJson',
                '',
                array()
            ),
        );
    }

    /**
     * Provides data for:
     *  - {@link self::testGetControllers}
     *  - {@link self::testGetControllersClasses}
     * Data: extension name
     *
     * @return array[] the data
     */
    public function extensionNameProvider()
    {
        return array(
            array('tao'),
        );
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Test {@link Factory::getControllerDescription}
     *
     * @dataProvider controllerNameProvider
     * @param $controllerClassName
     */
    public function testGetControllerDescription($controllerClassName)
    {
        $description = $this->factory->getControllerDescription($controllerClassName);

        $this->assertNotNull($description);
        $this->assertTrue($description instanceof ControllerDescription);
        $this->assertEquals($controllerClassName, $description->getClassName());
    }

    /**
     *
     * @dataProvider controllerActionNameProvider
     * @param $controllerClassName
     * @param $actionName
     * @param $descriptionStub
     * @param $rightsStub
     */
    public function testGetActionDescription($controllerClassName, $actionName, $descriptionStub, $rightsStub)
    {
        $description = $this->factory->getActionDescription($controllerClassName, $actionName);

        $this->assertTrue($description instanceof ActionDescription);
        $this->assertEquals($actionName, $description->getName());
        $this->assertEquals($descriptionStub, $description->getDescription());
        $this->assertEquals($rightsStub, $description->getRequiredRights());
    }

    /**
     * Test {@link Factory::getControllers}
     *
     * @dataProvider extensionNameProvider
     * @param $extensionId
     */
    public function testGetControllers($extensionId)
    {
        $controllers = $this->factory->getControllers($extensionId);
        $this->assertNotNull($controllers);
        $this->assertTrue(is_array($controllers));
        $this->assertContainsOnlyInstancesOf('oat\tao\model\controllerMap\ControllerDescription', $controllers);
    }

    /**
     *Test {@link Factory::isControllerClassNameValid}
     */
    public function testIsControllerClassNameValid()
    {
        $methodName = 'isControllerClassNameValid';

        $this->assertFalse( $this->invokeMethod($this->factory, $methodName, array('FakeStandaloneController') ), 'has valid descendant' );
        $this->assertFalse( $this->invokeMethod($this->factory, $methodName, array('FakeAbstractController') ), 'is not abstract' );
        $this->assertTrue(  $this->invokeMethod($this->factory, $methodName, array('FakeValidController') ), 'is valid' );
    }

    /**
     * Test {@link Factory::getControllerClasses}
     *
     * @dependsOn testIsControllerClassNameValid
     * @dataProvider extensionNameProvider
     * @param $extensionId
     */
    public function testGetControllersClasses($extensionId)
    {
        $extension = new common_ext_Extension( $extensionId );

        $controllers = $this->invokeMethod($this->factory, 'getControllerClasses', array($extension));
        $this->assertNotNull($controllers);
        $this->assertContainsOnly('string', $controllers, true);

        $validCount = 0;
        foreach( $controllers as $className ){
            if( $this->invokeMethod($this->factory, 'isControllerClassNameValid', array($className) ) ){
                $validCount++;
            }
        }

        $this->assertEquals(count($controllers), $validCount);
    }

} 