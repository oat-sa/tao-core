<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace oat\test\model;

/**
 * Description of ClassServiceTest
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class ClassServiceTest extends \oat\tao\test\TaoPhpUnitTestRunner {
    
    public function testDeleteResource() {
        
        $instance = $this->getMockForAbstractClass(\tao_models_classes_ClassService::class, 
                [], 
                '', 
                false, 
                false, 
                true,
                []
                );
        
        $resourceProphet = $this->prophesize(\core_kernel_classes_Resource::class);
        $resourceProphet->delete()->willReturn($resourceProphet);
        $resourceMock    = $resourceProphet->reveal();
        
        $this->assertSame($resourceMock, $instance->deleteResource($resourceMock));
        
    }
    
    public function testDeletePropertyIndex() {
        
        $instance = $this->getMockForAbstractClass(\tao_models_classes_ClassService::class, 
                [], 
                '', 
                false, 
                false, 
                true,
                []
                );
        
        $resourceProphet = $this->prophesize(\core_kernel_classes_Resource::class);
        $resourceProphet->delete(true)->willReturn($resourceProphet);
        $resourceMock    = $resourceProphet->reveal();
        
        $this->assertSame($resourceMock, $instance->deletePropertyIndex($resourceMock));
        
    }
    
    public function testDeleteClass() {

        $resources =[
            $this->prophesize(\core_kernel_classes_Resource::class)->reveal(),
            $this->prophesize(\core_kernel_classes_Resource::class)->reveal(),
        ];

        $fixtureRootClass = $this->prophesize(\core_kernel_classes_Class::class)->reveal();

        $instance = $this->getMockForAbstractClass(\tao_models_classes_ClassService::class,
            [],
            '',
            false,
            false,
            true,
            ['getRootClass', 'deleteClassProperty', 'deleteResource']
        );

        $instance->expects($this->exactly(2))->method('getRootClass')
                ->willReturn($fixtureRootClass);

        $properties =
                [
                    $this->prophesize(\core_kernel_classes_Property::class)->reveal(),
                    $this->prophesize(\core_kernel_classes_Property::class)->reveal(),
                    $this->prophesize(\core_kernel_classes_Property::class)->reveal(),
                    $this->prophesize(\core_kernel_classes_Property::class)->reveal(),
                ];

        $subClasses =
                [
                ];


        $instance->expects($this->exactly(4))
                ->method('deleteClassProperty')
                ->withConsecutive(
                        [$properties[0]],
                        [$properties[1]],
                        [$properties[2]],
                        [$properties[3]]
                        )
                ->willReturn(true);

        $instance->expects($this->exactly(2))
                ->method('deleteResource')
                ->withConsecutive(
                    [$resources[0]],
                    [$resources[1]]
                )
                ->willReturn(true);

        $classProphet  = $this->prophesize(\core_kernel_classes_Class::class);
        $classProphet->isSubClassOf($fixtureRootClass)->willReturn(true);
        $classProphet->equals($fixtureRootClass)->willReturn(false);
        $classProphet->getSubClasses(false)->willReturn($subClasses);
        $classProphet->getProperties()->willReturn($properties);
        $classProphet->delete()->willReturn(true);
        $classProphet->getInstances()->willReturn($resources);

        $classMock = $classProphet->reveal();
        
        $this->assertTrue($instance->deleteClass($classMock));
        
    }
    
}
