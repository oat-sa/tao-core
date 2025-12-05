<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\search;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\model\data\permission\PermissionHelper;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\test\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\search\ResultAccessChecker;
use oat\tao\model\TaoOntology;
use PHPUnit\Framework\MockObject\MockObject;

class ResultAccessCheckerTest extends TestCase
{
    /** @var ResultAccessChecker|MockObject */
    private $checker;
    private MockObject $serviceLocatorMock;
    private MockObject $permissionHelperMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceLocatorMock = $this->createMock(ServiceManager::class);
        $this->permissionHelperMock = $this->createMock(PermissionHelper::class);

        $this->serviceLocatorMock
            ->method('get')
            ->with(PermissionHelper::class)
            ->willReturn($this->permissionHelperMock);

        // Create a partial mock to override getResource and getClass methods
        $this->checker = $this->getMockBuilder(ResultAccessChecker::class)
            ->onlyMethods(['getResource', 'getClass'])
            ->getMock();

        $this->checker->setServiceLocator($this->serviceLocatorMock);
    }

    /**
     * Test that hasReadAccess returns true when resource type is an ontology class URI.
     * Ontology classes (schema/metadata) don't have permission records and should be skipped.
     */
    public function testHasReadAccessWithOntologyClassType(): void
    {
        $resourceId = 'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#Developer';
        $content = ['id' => $resourceId];

        // Mock the resource
        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);

        // Mock the type (ontology class)
        $typeMock = $this->createMock(core_kernel_classes_Resource::class);
        $typeMock->method('getUri')
            ->willReturn('http://www.tao.lu/Ontologies/TAOLTI.rdf#AclRole');

        $resourceMock->method('getUri')
            ->willReturn($resourceId);
        $resourceMock->method('getTypes')
            ->willReturn([$typeMock]);

        // Mock getResource to return our resource mock
        $this->checker->method('getResource')
            ->with($resourceId)
            ->willReturn($resourceMock);

        // Mock getClass for top level class
        $topLevelClassMock = $this->createMock(core_kernel_classes_Class::class);
        $this->checker->method('getClass')
            ->with(TaoOntology::CLASS_URI_OBJECT)
            ->willReturn($topLevelClassMock);

        // The permission helper should NOT be called for ontology class URIs
        $this->permissionHelperMock
            ->expects($this->never())
            ->method('filterByPermission');

        $result = $this->checker->hasReadAccess($content);

        $this->assertTrue($result, 'Should return true when type is an ontology class URI');
    }

    /**
     * Test that hasReadAccess returns true for RDF Schema ontology classes.
     */
    public function testHasReadAccessWithRdfSchemaType(): void
    {
        $resourceId = 'http://example.com/resource#123';
        $content = ['id' => $resourceId];

        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);

        $typeMock = $this->createMock(core_kernel_classes_Resource::class);
        $typeMock->method('getUri')
            ->willReturn('http://www.w3.org/2000/01/rdf-schema#Class');

        $resourceMock->method('getUri')
            ->willReturn($resourceId);
        $resourceMock->method('getTypes')
            ->willReturn([$typeMock]);

        $this->setupCheckerMocks($resourceId, $resourceMock);

        // Should skip permission check for RDF Schema classes
        $this->permissionHelperMock
            ->expects($this->never())
            ->method('filterByPermission');

        $result = $this->checker->hasReadAccess($content);

        $this->assertTrue($result, 'Should return true for RDF Schema ontology classes');
    }

    /**
     * Test that hasReadAccess returns true for RDF Syntax ontology classes.
     */
    public function testHasReadAccessWithRdfSyntaxType(): void
    {
        $resourceId = 'http://example.com/resource#456';
        $content = ['id' => $resourceId];

        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);

        $typeMock = $this->createMock(core_kernel_classes_Resource::class);
        $typeMock->method('getUri')
            ->willReturn('http://www.w3.org/1999/02/22-rdf-syntax-ns#Property');

        $resourceMock->method('getUri')
            ->willReturn($resourceId);
        $resourceMock->method('getTypes')
            ->willReturn([$typeMock]);

        $this->setupCheckerMocks($resourceId, $resourceMock);

        // Should skip permission check for RDF Syntax classes
        $this->permissionHelperMock
            ->expects($this->never())
            ->method('filterByPermission');

        $result = $this->checker->hasReadAccess($content);

        $this->assertTrue($result, 'Should return true for RDF Syntax ontology classes');
    }

    /**
     * Test that hasReadAccess checks permissions for non-ontology class types.
     * Regular data instances should have their permissions checked.
     */
    public function testHasReadAccessWithNonOntologyType(): void
    {
        $resourceId = 'http://example.com/data#item123';
        $typeUri = 'http://example.com/types#CustomType';
        $content = ['id' => $resourceId];

        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);

        $typeMock = $this->createMock(core_kernel_classes_Resource::class);
        $typeMock->method('getUri')
            ->willReturn($typeUri);

        $resourceMock->method('getUri')
            ->willReturn($resourceId);
        $resourceMock->method('getTypes')
            ->willReturn([$typeMock]);

        $classMock = $this->createMock(core_kernel_classes_Class::class);
        $classMock->method('getParentClasses')
            ->willReturn([]);

        $this->setupCheckerMocks($resourceId, $resourceMock, $classMock);

        // For non-ontology classes, permission should be checked
        $this->permissionHelperMock
            ->expects($this->once())
            ->method('filterByPermission')
            ->with([$typeUri], PermissionInterface::RIGHT_READ)
            ->willReturn([$typeUri]); // User has permission

        $result = $this->checker->hasReadAccess($content);

        $this->assertTrue($result, 'Should return true when user has permission on non-ontology type');
    }

    /**
     * Test that hasReadAccess returns false when user lacks permission on non-ontology type.
     */
    public function testHasReadAccessWithoutPermissionOnNonOntologyType(): void
    {
        $resourceId = 'http://example.com/data#item456';
        $typeUri = 'http://example.com/types#RestrictedType';
        $content = ['id' => $resourceId];

        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);

        $typeMock = $this->createMock(core_kernel_classes_Resource::class);
        $typeMock->method('getUri')
            ->willReturn($typeUri);

        $resourceMock->method('getUri')
            ->willReturn($resourceId);
        $resourceMock->method('getTypes')
            ->willReturn([$typeMock]);

        $this->setupCheckerMocks($resourceId, $resourceMock);

        // Permission check returns empty - user doesn't have permission
        $this->permissionHelperMock
            ->expects($this->once())
            ->method('filterByPermission')
            ->with([$typeUri], PermissionInterface::RIGHT_READ)
            ->willReturn([]); // No permission

        $result = $this->checker->hasReadAccess($content);

        $this->assertFalse($result, 'Should return false when user lacks permission on non-ontology type');
    }

    /**
     * Test mixed types: ontology class + non-ontology type.
     * Should skip ontology class check but verify permission on the non-ontology type.
     */
    public function testHasReadAccessWithMixedTypes(): void
    {
        $resourceId = 'http://example.com/data#mixed789';
        $ontologyTypeUri = 'http://www.tao.lu/Ontologies/TAO.rdf#Item';
        $customTypeUri = 'http://example.com/types#MyCustomType';
        $content = ['id' => $resourceId];

        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);

        // First type: ontology class (should be skipped)
        $ontologyTypeMock = $this->createMock(core_kernel_classes_Resource::class);
        $ontologyTypeMock->method('getUri')
            ->willReturn($ontologyTypeUri);

        // Second type: non-ontology class (should be checked)
        $customTypeMock = $this->createMock(core_kernel_classes_Resource::class);
        $customTypeMock->method('getUri')
            ->willReturn($customTypeUri);

        $resourceMock->method('getUri')
            ->willReturn($resourceId);
        $resourceMock->method('getTypes')
            ->willReturn([$ontologyTypeMock, $customTypeMock]);

        $classMock = $this->createMock(core_kernel_classes_Class::class);
        $classMock->method('getParentClasses')
            ->willReturn([]);

        $this->setupCheckerMocks($resourceId, $resourceMock, $classMock);

        // Only the non-ontology type should have permission checked
        $this->permissionHelperMock
            ->expects($this->once())
            ->method('filterByPermission')
            ->with([$customTypeUri], PermissionInterface::RIGHT_READ)
            ->willReturn([$customTypeUri]);

        $result = $this->checker->hasReadAccess($content);

        $this->assertTrue($result, 'Should return true when skipping ontology class and having permission on custom type');
    }

    /**
     * Test the isOntologyClass private method directly.
     */
    public function testIsOntologyClass(): void
    {
        // TAO Ontology classes
        $this->assertTrue(
            $this->invokePrivateMethod($this->checker, 'isOntologyClass', ['http://www.tao.lu/Ontologies/TAOLTI.rdf#AclRole']),
            'Should identify TAO ontology class'
        );

        $this->assertTrue(
            $this->invokePrivateMethod($this->checker, 'isOntologyClass', ['http://www.tao.lu/Ontologies/TAO.rdf#Item']),
            'Should identify TAO Item ontology class'
        );

        // RDF Schema classes
        $this->assertTrue(
            $this->invokePrivateMethod($this->checker, 'isOntologyClass', ['http://www.w3.org/2000/01/rdf-schema#Class']),
            'Should identify RDF Schema class'
        );

        $this->assertTrue(
            $this->invokePrivateMethod($this->checker, 'isOntologyClass', ['http://www.w3.org/2000/01/rdf-schema#Resource']),
            'Should identify RDF Schema Resource'
        );

        // RDF Syntax classes
        $this->assertTrue(
            $this->invokePrivateMethod($this->checker, 'isOntologyClass', ['http://www.w3.org/1999/02/22-rdf-syntax-ns#Property']),
            'Should identify RDF Syntax class'
        );

        // Non-ontology URIs
        $this->assertFalse(
            $this->invokePrivateMethod($this->checker, 'isOntologyClass', ['http://example.com/data#item123']),
            'Should not identify custom data URI as ontology class'
        );

        $this->assertFalse(
            $this->invokePrivateMethod($this->checker, 'isOntologyClass', ['http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#Developer']),
            'Should not identify LIS membership URI as ontology class'
        );
    }

    /**
     * Test that multiple ontology class types all get skipped.
     */
    public function testHasReadAccessWithMultipleOntologyTypes(): void
    {
        $resourceId = 'http://example.com/resource#multi';
        $content = ['id' => $resourceId];

        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);

        // All types are ontology classes
        $type1Mock = $this->createMock(core_kernel_classes_Resource::class);
        $type1Mock->method('getUri')
            ->willReturn('http://www.tao.lu/Ontologies/TAOLTI.rdf#AclRole');

        $type2Mock = $this->createMock(core_kernel_classes_Resource::class);
        $type2Mock->method('getUri')
            ->willReturn('http://www.w3.org/2000/01/rdf-schema#Class');

        $type3Mock = $this->createMock(core_kernel_classes_Resource::class);
        $type3Mock->method('getUri')
            ->willReturn('http://www.w3.org/1999/02/22-rdf-syntax-ns#Property');

        $resourceMock->method('getUri')
            ->willReturn($resourceId);
        $resourceMock->method('getTypes')
            ->willReturn([$type1Mock, $type2Mock, $type3Mock]);

        $this->setupCheckerMocks($resourceId, $resourceMock);

        // No permission checks should occur
        $this->permissionHelperMock
            ->expects($this->never())
            ->method('filterByPermission');

        $result = $this->checker->hasReadAccess($content);

        $this->assertTrue($result, 'Should return true when all types are ontology classes');
    }

    /**
     * Helper method to set up common mock expectations for getResource and getClass.
     */
    private function setupCheckerMocks(
        string $resourceId,
        core_kernel_classes_Resource $resourceMock,
        ?core_kernel_classes_Class $classMock = null
    ): void {
        // Mock getResource to return our resource mock
        $this->checker->method('getResource')
            ->with($resourceId)
            ->willReturn($resourceMock);

        // Mock getClass for top level class
        $topLevelClassMock = $this->createMock(core_kernel_classes_Class::class);
        $topLevelClassMock->method('getUri')
            ->willReturn(TaoOntology::CLASS_URI_OBJECT);

        // Set up getClass to return different mocks based on the URI
        $this->checker->method('getClass')
            ->willReturnCallback(function ($uri) use ($topLevelClassMock, $classMock) {
                if ($uri === TaoOntology::CLASS_URI_OBJECT) {
                    return $topLevelClassMock;
                }
                return $classMock ?? $this->createMock(core_kernel_classes_Class::class);
            });
    }

    /**
     * Helper method to invoke private methods for testing.
     * This is kept for testing the isOntologyClass private method directly.
     */
    private function invokePrivateMethod(object $object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}

