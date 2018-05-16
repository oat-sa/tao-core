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
 */
namespace oat\tao\model\import\service;

use common_report_Report;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;

class RdsValidatorValueMapper extends ConfigurableService implements ImportValueMapperInterface
{
    use OntologyAwareTrait;

    const OPTION_CLASS = 'class';
    const OPTION_PROPERTY = 'property';

    /** @var common_report_Report */
    protected $report;

    /**
     * @param string $value
     * @return mixed
     * @throws RdsResourceNotFoundException
     */
    public function map($value)
    {
        $class = $this->getClass($this->getOption(static::OPTION_CLASS));

        if (is_null($this->getOption(static::OPTION_PROPERTY))){
            $results = [$class->getResource($value)];
        }else{
            $results = $class->searchInstances(
                [ $this->getOption(static::OPTION_PROPERTY) => $value ],
                [ 'like' => false, 'recursive' => true ]);
        }
        if (count($results) === 0){
            throw new RdsResourceNotFoundException('No resource found for class: '. $this->getOption(static::OPTION_CLASS). ' value: '.$value);
        }

        if (count($results) > 1){
            throw new RdsResourceNotFoundException('Multiple values has been found for class: '. $this->getOption(static::OPTION_CLASS) . ' value :'.$value);
        }

        $resource = reset($results);
        if (!$resource->isInstanceOf($class)){
            throw new RdsResourceNotFoundException('Resource is not a class: '. $this->getOption(static::OPTION_CLASS));
        }

        $this->report = common_report_Report::createSuccess('Resource mapped with success: '.$class.':'.$value);

        return $resource;
    }

    /**
     * @return common_report_Report
     */
    public function getReport()
    {
        return $this->report;
    }

}