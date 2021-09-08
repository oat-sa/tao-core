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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\DataAccess\Repository;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use tao_helpers_form_GenerisFormFactory;
use common_persistence_Manager;
use common_persistence_SqlPersistence as SqlPersistence;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Connection;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\cache\SimpleCache;
use Psr\SimpleCache\CacheInterface;
use oat\tao\model\Lists\Business\Domain\DependsOnProperty;
use oat\tao\model\Specification\PropertySpecificationInterface;
use oat\tao\model\Lists\Business\Domain\DependsOnPropertyCollection;
use oat\tao\model\Lists\Business\Specification\DependentPropertySpecification;
use oat\tao\model\Lists\Business\Specification\RemoteListPropertySpecification;

class DependsOnPropertyRepository extends ConfigurableService
{
    private const CACHE_MASK = 'dependsOnProperty-%s-%s';

    /** @var core_kernel_classes_Property[] */
    private $properties;

    /** @var PropertySpecificationInterface */
    private $remoteListPropertySpecification;

    /** @var PropertySpecificationInterface */
    private $dependentPropertySpecification;

    public function withProperties(array $properties)
    {
        $this->properties = $properties;
    }

    public function deleteCache(array $options): void
    {
        if (!isset($options['propertyUri'])) {
            // remove or find list uri items
            // find all properties with list Uris
            $parentPropertiesList = $this->getpropertiesByListUris([$options['listUri']]);
            if (empty($parentPropertiesList)) {
                return;
            }
            foreach ($parentPropertiesList as $property) {
                $this->getCache()->delete(sprintf(self::CACHE_MASK, $property, $options['listUri']));
            }
        }
        $this->getCache()->delete(sprintf(self::CACHE_MASK, $options['propertyUri'], $options['listUri']));
    }
    public function updateCache($property, $listUri): void
    {
        $this->findAll(
            [
                    'property' => $property,
                    'listUri'  => $listUri,
                    'updateCache'  => true
                ]
        );
    }
    public function findAll(array $options): DependsOnPropertyCollection
    {
        $collection = new DependsOnPropertyCollection();

        /** @var core_kernel_classes_Property $property */
        $property = $options['property'];

        if (
            !$property->getDomain()->count()
            || !$this->getRemoteListPropertySpecification()->isSatisfiedBy($property)
        ) {
            return $collection;
        }
        $listUri = $options['listUri'] ?? $property->getRange()->getUri();
        $dependentsOnPropertyJson = $this->getCache()->get(sprintf(self::CACHE_MASK, $property->getUri(), $listUri));
        if (is_null($dependentsOnPropertyJson) || $options['updateCache'] === true) {
            $dependencies = $this->getDependencies($listUri);
            if (empty($dependencies)) {
                $this->getCache()->set(sprintf(self::CACHE_MASK, $property->getUri(), $listUri), "false");
                return $collection;
            }

            $dependencyListUris = $this->getDependencyListUris($dependencies);
            if (empty($dependencyListUris)) {
                $this->getCache()->set(sprintf(self::CACHE_MASK, $property->getUri(), $listUri), "false");
                return $collection;
            }
            $this->getCache()->set(
                sprintf(self::CACHE_MASK, $property->getUri(), $listUri),
                json_encode(array("uri" => $dependencyListUris))
            );
        }
        if (!empty($dependentsOnPropertyJson)) {
            if ($dependentsOnPropertyJson == "false") {
                return $collection;
            }
            echo $dependentsOnPropertyJson;
            $dependencyListUris = json_decode($dependentsOnPropertyJson)->uri;
        }
        $parentPropertiesList = $this->getpropertiesByListUris($dependencyListUris);
        
        if (empty($parentPropertiesList)) {
            return $collection;
        }

        /** @var core_kernel_classes_Class $class */
        $class = $property->getDomain()->get(0);
        $propertyUri = $property->getUri();
        

        /** @var core_kernel_classes_Property $property */
        foreach ($this->getProperties($class) as $classProperty) {
            print_r($parentPropertiesList);
            if (
                $propertyUri === $classProperty->getUri()
                || !$this->getRemoteListPropertySpecification()->isSatisfiedBy($classProperty)
            ) {
                continue;
            }
            if (
                !$this->getDependentPropertySpecification()->isSatisfiedBy($classProperty)
                && in_array($classProperty->getUri(), $parentPropertiesList, true)
            ) {
                $collection->append(new DependsOnProperty($classProperty));

                continue;
            }

            // @TODO Check for parent's (current property) children outside the foreach statement
            // if ($propertyUri === $classProperty->getDependsOnPropertyCollection()->current()->getUri()) {
            //     return new DependsOnPropertyCollection();
            // }
        }
        return $collection;
    }

    private function getProperties(core_kernel_classes_Class $class): array
    {
        return $this->properties ?? tao_helpers_form_GenerisFormFactory::getClassProperties($class);
    }

    private function getRemoteListPropertySpecification(): PropertySpecificationInterface
    {
        if (!isset($this->remoteListPropertySpecification)) {
            $this->remoteListPropertySpecification = $this->getServiceLocator()->get(
                RemoteListPropertySpecification::class
            );
        }

        return $this->remoteListPropertySpecification;
    }

    private function getDependentPropertySpecification(): PropertySpecificationInterface
    {
        if (!isset($this->dependentPropertySpecification)) {
            $this->dependentPropertySpecification = $this->getServiceLocator()->get(
                DependentPropertySpecification::class
            );
        }

        return $this->dependentPropertySpecification;
    }

    private function getPersistence(): SqlPersistence
    {
        return $this->getServiceManager()->get(common_persistence_Manager::SERVICE_ID)->getPersistenceById('default');
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this->getPersistence()->getPlatform()->getQueryBuilder();
    }

    private function getDependencies($remoteListUri): array
    {
        $query = $this->getQueryBuilder();
        $expressionBuilder = $query->expr();

        $query
            ->select('value')
            ->from('list_items_dependencies', 'dependencies')
            ->innerJoin(
                'dependencies',
                'list_items',
                'items',
                $expressionBuilder->eq('dependencies.list_item_id', 'items.id')
            )
            ->andWhere($expressionBuilder->eq('items.list_uri', ':label_uri'))
            ->setParameter('label_uri', $remoteListUri);

        return $query->execute()->fetchAll(FetchMode::COLUMN);
    }

    private function getDependencyListUris($dependencies): array
    {
        $query = $this->getQueryBuilder();
        $expressionBuilder = $query->expr();
        $query
            ->select('list_uri')
            ->from('list_items')
            ->andWhere($expressionBuilder->in('list_items.uri', ':label_uri'))
            ->groupBy('list_uri')
            ->setParameter('label_uri', $dependencies, Connection::PARAM_STR_ARRAY);
        return $query->execute()->fetchAll(FetchMode::COLUMN);
    }

    private function getpropertiesByListUris($dependencyListUris): array
    {
        $query = $this->getQueryBuilder();
        $expressionBuilder = $query->expr();
        $query
            ->select('subject')
            ->from('statements')
            ->andWhere($expressionBuilder->eq('predicate', ':predicate'))
            ->andWhere($expressionBuilder->in('object', ':object'))
            ->setParameters(
                [
                'predicate' => OntologyRdfs::RDFS_RANGE,
                'object' => $dependencyListUris
                ],
                [
                    'object' => Connection::PARAM_STR_ARRAY
                ]
            );
        return $query->execute()->fetchAll(FetchMode::COLUMN);
    }

    private function getCache(): CacheInterface
    {
        return $this->getServiceLocator()->get(SimpleCache::SERVICE_ID);
    }
}
