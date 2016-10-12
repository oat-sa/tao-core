<?php

namespace oat\tao\test\model\metadata;

use oat\oatbox\service\ServiceManager;
use oat\tao\model\metadata\dataSource\iterator\CsvIterator;
use oat\tao\model\metadata\import\MetadataImporter;
use oat\tao\model\metadata\import\OntologyMetadataImport;
use oat\tao\test\TaoPhpUnitTestRunner;

class MetadataServiceTest extends TaoPhpUnitTestRunner
{
    /**
     * @todo please remove items ref from here...
     */
    public function testLoading()
    {
        try {
            \common_ext_ExtensionsManager::singleton()->getExtensionById('taoItems');
            $path = __DIR__ . '/../../export/samples/withColNames.csv';

            /** @var OntologyMetadataImport $metadataImporter */
            $metadataImporter = ServiceManager::getServiceManager()->get(MetadataImporter::SERVICE_ID);
            $metadataImporter->import(new CsvIterator($path, ';'));
        } catch (\Exception $e) {
            $this->fail('Import method fail with message: ' . $e->getMessage());
        }
        $this->assertTrue(true);
    }
}