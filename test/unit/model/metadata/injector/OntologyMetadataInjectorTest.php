<?php

declare(strict_types=1);

namespace oat\tao\test\unit\model\metadata\import;

use oat\generis\test\TestCase;
use oat\tao\model\metadata\exception\InconsistencyConfigException;
use oat\tao\model\metadata\exception\injector\MetadataInjectorReadException;
use oat\tao\model\metadata\exception\injector\MetadataInjectorWriteException;
use oat\tao\model\metadata\exception\reader\MetadataReaderNotFoundException;
use oat\tao\model\metadata\exception\writer\MetadataWriterException;
use oat\tao\model\metadata\injector\OntologyMetadataInjector;
use oat\tao\model\metadata\reader\Reader;
use oat\tao\model\metadata\writer\ontologyWriter\OntologyWriter;

class OntologyMetadataInjectorTest extends TestCase
{
    public function testSetOptions(): void
    {
        $options = ['source' => ['test'], 'destination' => ['test']];
        $ontologyInjector = $this->getOntologyMetadataInjectorMock();

        $ontologyInjector->setOptions($options);
        $this->assertSame($options, $ontologyInjector->getOptions());
    }

    /**
     * @dataProvider setOptionsProviderException
     */
    public function testSetOptionsException($options, $exception): void
    {
        $ontologyInjector = $this->getOntologyMetadataInjectorMock();
        $this->expectException($exception);
        $ontologyInjector->setOptions($options);
    }

    public function setOptionsProviderException()
    {
        return [
            // Empty config
            [[], InconsistencyConfigException::class],

            // No source
            [['unknown' => ['test'], 'destination' => ['test']], InconsistencyConfigException::class],
            // Source not an array
            [['source' => 'test', 'destination' => ['test']], InconsistencyConfigException::class],
            // Source is empty array
            [['source' => [], 'destination' => ['test']], InconsistencyConfigException::class],

            // No destination
            [['source' => ['test'], 'unknown' => ['test']], InconsistencyConfigException::class],
            // Destination not an array
            [['source' => ['test'], 'destination' => 'test'], InconsistencyConfigException::class],
            // Destination is empty array
            [['source' => ['test'], 'destination' => []], InconsistencyConfigException::class],
        ];
    }

    public function testCreateInjectorHelpers(): void
    {
        $sourceFixture = ['sourceFixture'];
        $destinationFixture = ['destinationFixture'];

        $ontologyInjector = $this->getOntologyMetadataInjectorMock(['getOption', 'setReaders', 'setWriters']);

        $ontologyInjector->expects($this->exactly(2))
            ->method('getOption')
            ->withConsecutive(
                $this->stringContains('source'),
                $this->stringContains('destination')
            )
            ->willReturnOnConsecutiveCalls(
                $sourceFixture,
                $destinationFixture
            );

        $ontologyInjector->expects($this->once())
            ->method('setReaders')
            ->with($this->equalTo($sourceFixture));

        $ontologyInjector->expects($this->once())
            ->method('setWriters')
            ->with($this->equalTo($destinationFixture));

        $ontologyInjector->createInjectorHelpers();
    }

    public function testRead(): void
    {
        $dataSourceFixture = ['dataSourceFixture'];

        $readerMock1 = $this->getMockForAbstractClass(Reader::class, [], '', false, true, true, ['getValue']);
        $readerMock1->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo($dataSourceFixture))
            ->willReturn('polop1');

        $readerMock2 = $this->getMockForAbstractClass(Reader::class, [], '', false, true, true, ['getValue']);
        $readerMock2->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo($dataSourceFixture))
            ->willReturn('polop2');

        $readers = [
            'test1' => $readerMock1,
            'test2' => $readerMock2,
        ];

        $ontologyInjector = $this->getOntologyMetadataInjectorMock();

        $property = new \ReflectionProperty(get_class($ontologyInjector), 'readers');
        $property->setAccessible(true);
        $property->setValue($ontologyInjector, $readers);

        $data = $ontologyInjector->read($dataSourceFixture);

        $this->assertSame(['test1' => 'polop1', 'test2' => 'polop2'], $data);
    }

    public function testReadException(): void
    {
        $dataSourceFixture = ['dataSourceFixture'];

        $readerMock1 = $this->getMockForAbstractClass(Reader::class, [], '', false, true, true, ['getValue']);
        $readerMock1->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo($dataSourceFixture))
            ->willThrowException(new MetadataReaderNotFoundException());

        $readers = ['test1' => $readerMock1];

        $ontologyInjector = $this->getOntologyMetadataInjectorMock();

        $property = new \ReflectionProperty(get_class($ontologyInjector), 'readers');
        $property->setAccessible(true);
        $property->setValue($ontologyInjector, $readers);

        $this->expectException(MetadataInjectorReadException::class);
        $ontologyInjector->read($dataSourceFixture);
    }

    public function testSetReaders(): void
    {
        $readersFixture = [
            'reader1' => ['key' => 'polop1'],
            'reader2' => ['key' => 'polop2'],
            'reader3' => ['key' => 'polop3'],
        ];

        $ontologyInjector = $this->getOntologyMetadataInjectorMock();

        $method = new \ReflectionMethod(get_class($ontologyInjector), 'setReaders');
        $method->setAccessible(true);
        $method->invokeArgs($ontologyInjector, [$readersFixture]);

        $property = new \ReflectionProperty(get_class($ontologyInjector), 'readers');
        $property->setAccessible(true);
        $readers = $property->getValue($ontologyInjector);

        foreach ($readers as $name => $reader) {
            $property = new \ReflectionProperty(get_class($reader), 'key');
            $property->setAccessible(true);
            $key = $property->getValue($reader);

            $this->assertSame($readersFixture[$name]['key'], $key);
        }
    }

    public function testSetWriters(): void
    {
        $writersFixture = [
            'writer1' => 'polop1',
            'writer2' => 'polop2',
            'writer3' => 'polop3',
        ];

        $ontologyInjector = $this->getOntologyMetadataInjectorMock(['buildService']);

        $ontologyInjector->expects($this->exactly(3))
            ->method('buildService')
            ->withConsecutive(
                $this->stringContains('polop1'),
                $this->stringContains('polop2'),
                $this->stringContains('polop3')
            )
            ->willReturnOnConsecutiveCalls(
                'writerFixture1',
                'writerFixture2',
                'writerFixture3'
            );

        $method = new \ReflectionMethod(get_class($ontologyInjector), 'setWriters');
        $method->setAccessible(true);
        $method->invokeArgs($ontologyInjector, [$writersFixture]);

        $property = new \ReflectionProperty(get_class($ontologyInjector), 'writers');
        $property->setAccessible(true);
        $writers = $property->getValue($ontologyInjector);

        $this->assertArrayHasKey('writer1', $writers);
        $this->assertSame($writers['writer1'], 'writerFixture1');
        $this->assertArrayHasKey('writer2', $writers);
        $this->assertSame($writers['writer2'], 'writerFixture2');
        $this->assertArrayHasKey('writer3', $writers);
        $this->assertSame($writers['writer3'], 'writerFixture3');
    }

    public function testWrite(): void
    {
        $resource = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data = ['dataFixture'];
        $dryrun = 'dryrunFixture';

        $writerMock = $this->getMockForAbstractClass(OntologyWriter::class, [], '', false, true, true, ['validate', 'write', 'format']);
        $writerMock->expects($this->once())
            ->method('format')
            ->with($this->equalTo($data))
            ->willReturn($data);

        $writerMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($data))
            ->willReturn(true);

        $writerMock->expects($this->once())
            ->method('write')
            ->willReturn(true);

        try {
            $ontologyInjector = $this->getOntologyMetadataInjectorMock();
            $property = new \ReflectionProperty(get_class($ontologyInjector), 'writers');
            $property->setAccessible(true);
            $property->setValue($ontologyInjector, [$writerMock]);

            $ontologyInjector->write($resource, $data, $dryrun);
        } catch (MetadataInjectorWriteException $e) {
            $this->fail('Exception during test injector write with message : ' . $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function testWriteExceptionNotOntologyWriter(): void
    {
        $resource = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data = ['dataFixture'];
        $dryrun = 'dryrunFixture';

        $writerMock = $this->getMockForAbstractClass(\stdClass::class, [], '', false, true, true, ['validate', 'format']);

        $writerMock->expects($this->once())
            ->method('format')
            ->with($this->equalTo($data))
            ->willReturn($data);

        $writerMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($data))
            ->willReturn(true);

        $ontologyInjector = $this->getOntologyMetadataInjectorMock();
        $property = new \ReflectionProperty(get_class($ontologyInjector), 'writers');
        $property->setAccessible(true);
        $property->setValue($ontologyInjector, [$writerMock]);

        $this->expectException(MetadataInjectorWriteException::class);
        $ontologyInjector->write($resource, $data, $dryrun);
    }

    public function testWriteExceptionCannotValidate(): void
    {
        $resource = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data = ['dataFixture'];
        $dryrun = 'dryrunFixture';

        $writerMock = $this->getMockForAbstractClass(\stdClass::class, [], '', false, true, true, ['validate', 'format']);

        $writerMock->expects($this->once())
            ->method('format')
            ->with($this->equalTo($data))
            ->willReturn($data);

        $writerMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($data))
            ->willReturn(false);

        $ontologyInjector = $this->getOntologyMetadataInjectorMock();
        $property = new \ReflectionProperty(get_class($ontologyInjector), 'writers');
        $property->setAccessible(true);
        $property->setValue($ontologyInjector, [$writerMock]);

        $this->expectException(MetadataInjectorWriteException::class);
        $ontologyInjector->write($resource, $data, $dryrun);
    }

    public function testWriteExceptionCannotWriteValue(): void
    {
        $resource = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data = ['dataFixture'];
        $dryrun = 'dryrunFixture';

        $writerMock = $this->getMockForAbstractClass(OntologyWriter::class, [], '', false, true, true, ['validate', 'format', 'write']);

        $writerMock->expects($this->once())
            ->method('format')
            ->with($this->equalTo($data))
            ->willReturn($data);

        $writerMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($data))
            ->willReturn(true);

        $writerMock->expects($this->once())
            ->method('write')
            ->willThrowException(new MetadataWriterException());

        $ontologyInjector = $this->getOntologyMetadataInjectorMock();
        $property = new \ReflectionProperty(get_class($ontologyInjector), 'writers');
        $property->setAccessible(true);
        $property->setValue($ontologyInjector, [$writerMock]);

        $this->expectException(MetadataInjectorWriteException::class);
        $ontologyInjector->write($resource, $data, $dryrun);
    }

    protected function getOntologyMetadataInjectorMock($methods = [])
    {
        return $this->getMockForAbstractClass(OntologyMetadataInjector::class, [], '', false, true, true, $methods);
    }
}
