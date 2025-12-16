<?php

namespace oat\tao\test\unit\model\metadata\import;

use PHPUnit\Framework\TestCase;
use oat\tao\model\metadata\exception\writer\MetadataWriterException;
use oat\tao\model\metadata\writer\ontologyWriter\PropertyWriter;

class PropertyWriterTest extends TestCase
{
    public function testWrite()
    {
        $data = ['datavalue'];

        $resource = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->setConstructorArgs(['uriResource'])
            ->onlyMethods(['editPropertyValues'])
            ->getMock();
        $resource->expects($this->once())
            ->method('editPropertyValues')
            ->willReturn(true);

        $property = $this->getMockBuilder(\core_kernel_classes_Property::class)
            ->setConstructorArgs(['uriProperty'])
            ->getMock();

        $writer = $this->getMockBuilder(PropertyWriter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate', 'getPropertyToWrite'])
            ->getMock();

        $writer->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $writer->expects($this->exactly(2))
            ->method('getPropertyToWrite')
            ->willReturn($property);

        $writer->write($resource, $data);
    }

    public function testWriteInvalid()
    {
        $data = ['datavalue'];

        $resource = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $property = $this->getMockBuilder(\core_kernel_classes_Property::class)
            ->setConstructorArgs(['uriProperty'])
            ->getMock();

        $writer = $this->getMockBuilder(PropertyWriter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate', 'getPropertyToWrite'])
            ->getMock();
        $writer->expects($this->once())
            ->method('validate')
            ->willReturn(false);

        $writer->expects($this->exactly(1))
            ->method('getPropertyToWrite')
            ->willReturn($property);

        $this->expectException(MetadataWriterException::class);
        $writer->write($resource, $data);
    }

    public function testWriteInDryrunMode()
    {
        $data = ['datavalue'];

        $resource = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->setConstructorArgs(['uriResource'])
            ->onlyMethods(['setPropertyValue'])
            ->getMock();
        $resource->expects($this->never())
            ->method('setPropertyValue');

        $property = $this->getMockBuilder(\core_kernel_classes_Property::class)
            ->setConstructorArgs(['uriProperty'])
            ->getMock();

        $writer = $this->getMockBuilder(PropertyWriter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate', 'getPropertyToWrite'])
            ->getMock();
        $writer->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $writer->expects($this->exactly(1))
            ->method('getPropertyToWrite')
            ->willReturn($property);

        $writer->write($resource, $data, true);
    }

    public function testWriteWithUnsuccessResourceWriting()
    {
        $data = ['datavalue'];

        $resource = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->setConstructorArgs(['uriResource'])
            ->onlyMethods(['editPropertyValues'])
            ->getMock();
        $resource->expects($this->once())
            ->method('editPropertyValues')
            ->willReturn(false);

        $property = $this->getMockBuilder(\core_kernel_classes_Property::class)
            ->setConstructorArgs(['uriProperty'])
            ->getMock();

        $writer = $this->getMockBuilder(PropertyWriter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate', 'getPropertyToWrite'])
            ->getMock();
        $writer->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $writer->expects($this->exactly(2))
            ->method('getPropertyToWrite')
            ->willReturn($property);

        $this->expectException(MetadataWriterException::class);
        $writer->write($resource, $data);
    }
}
