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
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\test\integration\model\resources;

use oat\tao\test\TaoPhpUnitTestRunner;

use oat\tao\model\resources\ResourceIterator;
use oat\generis\model\OntologyRdfs;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\helper\SupportedOperatorHelper;
use oat\oatbox\service\ServiceManager;

/**
 * Class ResourceIteratorTest
 * @package oat\tao\model\search\index
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ResourceIteratorTest extends TaoPhpUnitTestRunner
{

    public function tearDown()
    {
        $this->removeResources();
    }

    public function testNext()
    {
        $this->removeResources();
        $this->loadResources();

        /** @var ComplexSearchService $search */
        $search = ServiceManager::getServiceManager()->get(ComplexSearchService::SERVICE_ID);
        $queryBuilder = $search->query();

        $criteria = $queryBuilder->newQuery();
        $criteria->addCriterion(OntologyRdfs::RDFS_LABEL, SupportedOperatorHelper::GREATER_THAN_EQUAL, 4);
        $iterator = new ResourceIterator($this->getClasses(), $criteria);
        $iterator->setServiceLocator(ServiceManager::getServiceManager());
        $this->assertTrue($iterator->current() === null);
        $this->assertTrue($iterator->valid() === false);

        $queryBuilder = $search->query();
        $criteria = $queryBuilder->newQuery();
        $criteria->addCriterion(OntologyRdfs::RDFS_LABEL, SupportedOperatorHelper::GREATER_THAN_EQUAL, 2);
        $iterator = new ResourceIterator($this->getClasses(), $criteria);
        $iterator->setServiceLocator(ServiceManager::getServiceManager());
        $resultArray = [];
        $resultArray[] = $iterator->current()->getLabel();
        $iterator->next();
        $resultArray[] = $iterator->current()->getLabel();
        $iterator->next();
        sort($resultArray);
        $this->assertEquals(['2', '3'], $resultArray);
        $this->assertTrue($iterator->valid() === false);


        $criteria = $queryBuilder->newQuery();
        $criteria->addCriterion(OntologyRdfs::RDFS_LABEL, SupportedOperatorHelper::GREATER_THAN_EQUAL, 3);
        $iterator = new ResourceIterator($this->getClasses(), $criteria);
        $iterator->setServiceLocator(ServiceManager::getServiceManager());
        $this->assertEquals('3', $iterator->current()->getLabel());
        $iterator->next();
        $this->assertTrue($iterator->valid() === false);


        $iterator = new ResourceIterator($this->getClasses());
        $iterator->setServiceLocator(ServiceManager::getServiceManager());
        $resultArray = [];
        $resultArray[] = $iterator->current()->getLabel();
        $iterator->next();
        $resultArray[] = $iterator->current()->getLabel();
        $iterator->next();
        $resultArray[] = $iterator->current()->getLabel();
        $iterator->next();
        sort($resultArray);
        $this->assertEquals(['1', '2', '3'], $resultArray);
        $this->assertTrue($iterator->valid() === false);
    }

    public function testLoadResources()
    {
        $this->removeResources();
        $this->loadResources(ResourceIterator::CACHE_SIZE * 2);
        $iterator = new ResourceIterator($this->getClasses());
        $iterator->setServiceLocator(ServiceManager::getServiceManager());
        $n = 0;
        foreach ($iterator as $resource) {
            $n++;
        }
        $this->assertEquals(ResourceIterator::CACHE_SIZE * 2, $n);
    }

    private function removeResources()
    {
        $classes = $this->getClasses();
        foreach ($classes as $class) {
            foreach ($class->getInstances() as $instance) {
                $instance->delete();
            }
        }
    }

    private function loadResources($amount = 3)
    {
        $classes = $this->getClasses();
        for ($i = 1; $i <= $amount; $i++) {
            $classes[$i%2]->createInstance($i);
        }
    }

    private function getClasses()
    {
        return [
            new \core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAO.rdf#ResourceIteratorTest1'),
            new \core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAO.rdf#ResourceIteratorTest2'),
        ];
    }
}
