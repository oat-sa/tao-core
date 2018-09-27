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

namespace oat\tao\model\resources;

use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\base\QueryInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use core_kernel_classes_Resource;
use core_kernel_classes_Class;

/**
 * Class ResourceIterator
 * @package oat\tao\model\search\index
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ResourceIterator extends \core_kernel_classes_ResourceIterator implements ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    private $classessUsedInCriteria = [];

    /** @var QueryInterface */
    private $criteria;

    /**
     * @inheritdoc
     * @param array $criteria
     */
    public function __construct($classes, QueryInterface $criteria = null)
    {
        parent::__construct($classes);
        $this->criteria = $criteria;
    }

    /**
     * Load resources from storage
     *
     * @param core_kernel_classes_Class $class
     * @param integer $offset
     * @return core_kernel_classes_Resource[]
     */
    protected function loadResources(core_kernel_classes_Class $class, $offset)
    {
        $search = $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
        $queryBuilder = $search->query()->setLimit(self::CACHE_SIZE)->setOffset($offset);

        $criteria = $search->searchType($queryBuilder, $class->getUri(), false);
        if ($this->criteria !== null) {
            foreach ($this->criteria->getStoredQueryCriteria() as $storedQueryCriterion) {
                $criteria->addCriterion(
                    $storedQueryCriterion->getName(),
                    $storedQueryCriterion->getOperator(),
                    $storedQueryCriterion->getValue()
                );
            }
        }
        $queryBuilder = $queryBuilder->setCriteria($criteria);
        return $search->getGateway()->search($queryBuilder);
    }
}
