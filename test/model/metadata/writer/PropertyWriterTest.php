<?php

namespace oat\tao\test\model\metadata\import;

use oat\tao\model\metadata\exception\writer\MetadataWriterException;
use oat\tao\model\metadata\writer\ontologyWriter\PropertyWriter;

class PropertyWriterTest extends \PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $data = ['datavalue'];

        $resource = $this->getMock(\core_kernel_classes_Resource::class, ['setPropertyValue'], ['uriResource']);
        $resource->expects($this->once())
            ->method('setPropertyValue')
            ->willReturn(true);

        $property = $this->getMock(\core_kernel_classes_Property::class, [], ['uriProperty']);

        $writer = $this->getMock(PropertyWriter::class, ['validate', 'getPropertyToWrite'], [], '', false);
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

        $resource = $this->getMock(\core_kernel_classes_Resource::class, [], [], '', false);

        $property = $this->getMock(\core_kernel_classes_Property::class, [], ['uriProperty']);

        $writer = $this->getMock(PropertyWriter::class, ['validate', 'getPropertyToWrite'], [], '', false);
        $writer->expects($this->once())
            ->method('validate')
            ->willReturn(false);

        $writer->expects($this->exactly(1))
            ->method('getPropertyToWrite')
            ->willReturn($property);

        $this->setExpectedException(MetadataWriterException::class);
        $writer->write($resource, $data);
    }

    public function testWriteInDryrunMode()
    {
        $data = ['datavalue'];

        $resource = $this->getMock(\core_kernel_classes_Resource::class, ['setPropertyValue'], ['uriResource']);
        $resource->expects($this->never())
            ->method('setPropertyValue');

        $property = $this->getMock(\core_kernel_classes_Property::class, [], ['uriProperty']);

        $writer = $this->getMock(PropertyWriter::class, ['validate', 'getPropertyToWrite'], [], '', false);
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

        $resource = $this->getMock(\core_kernel_classes_Resource::class, ['setPropertyValue'], ['uriResource']);
        $resource->expects($this->once())
            ->method('setPropertyValue')
            ->willReturn(false);

        $property = $this->getMock(\core_kernel_classes_Property::class, [], ['uriProperty']);

        $writer = $this->getMock(PropertyWriter::class, ['validate', 'getPropertyToWrite'], [], '', false);
        $writer->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $writer->expects($this->exactly(2))
            ->method('getPropertyToWrite')
            ->willReturn($property);

        $this->setExpectedException(MetadataWriterException::class);
        $writer->write($resource, $data);
    }

}