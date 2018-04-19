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
use oat\generis\model\OntologyRdf;
use oat\search\helper\SupportedOperatorHelper;
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
        if ($this->criteria !== null) {
            $this->criteria->addCriterion(OntologyRdf::RDF_TYPE, SupportedOperatorHelper::EQUAL, $class->getUri());
        } else {
            $this->criteria = $search->searchType($queryBuilder, $class->getUri(), false);
        }
        $queryBuilder->setCriteria($this->criteria);
        return $search->getGateway()->search($queryBuilder);
    }
}
