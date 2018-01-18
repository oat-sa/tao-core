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
 * Copyright (c) 2016 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\metadata\writer\ontologyWriter;

use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\metadata\exception\InconsistencyConfigException;
use oat\tao\model\metadata\exception\reader\MetadataReaderNotFoundException;

/**
 * Class PropertyWriter
 * Writer to write one value to a property
 *
 * @author Camille Moyon
 * @package oat\tao\model\metadata\writer\ontologyWriter
 */
class ListPropertyWriter extends PropertyWriter
{
    protected $list;

    /**
     * ListPropertyWriter constructor.
     *
     * @param array $params
     * @throws InconsistencyConfigException
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        $listClass = $this->getClass(
            $this->getClass($this->getOption(self::PROPERTY_KEY))
                 ->getOnePropertyValue($this->getProperty(OntologyRdfs::RDFS_RANGE))
        );

        $list = $this->getListService()->getListElements($listClass);

        if (empty($list)) {
            throw new InconsistencyConfigException('List "' . $listClass->getUri() . '" does not contain element or not correctly configured.');
        }

        /** @var \core_kernel_classes_Resource $element */
        foreach ($list as $element) {
            $this->list[$element->getUri()] = [
                $element->getLabel(),
                $element->getOnePropertyValue($this->getProperty(OntologyRdf::RDF_VALUE))->literal
            ];
        }

    }

    /**
     * Format an array to expected value to be written
     *
     * @param array $data
     * @return int|string
     * @throws MetadataReaderNotFoundException
     */
    public function format(array $data)
    {
        $value = parent::format($data);
        foreach ($this->list as $uri => $values) {
            if (in_array($value, $values)) {
                return $uri;
            }
        }
        return '';
    }

    /**
     * Return the list associated to self::PROPERTY_KEY property
     *
     * @return\tao_models_classes_ListService
     */
    protected function getListService()
    {
        return \tao_models_classes_ListService::singleton();
    }
}