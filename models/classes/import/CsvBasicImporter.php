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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\import;


/**
 * Basic import of csv files
 *
 * @access public
 * @author Antoine Robin, <antoine.robin@vesperiagroup.com>
 * @package tao
 */
class CSVBasicImporter extends CsvAbstractImporter
{
    const OPTION_POSTFIX = '_O';

    public function import($class, $options)
    {
        return parent::importFile($class, $options);

    }

    /**
     * @param \core_kernel_classes_Class $class
     * @param string $file
     * @param array $options
     * @return array
     */
    public function getCsvMapping($class, $file, $options)
    {
        $properties = $this->getClassProperties($class);
        $csv_data = new \tao_helpers_data_CsvFile($options);
        $csv_data->load($file);
        $firstRowAsColumnNames = (isset($options[\tao_helpers_data_CsvFile::FIRST_ROW_COLUMN_NAMES]))?$options[\tao_helpers_data_CsvFile::FIRST_ROW_COLUMN_NAMES]:false;
        $header = $this->getColumnMapping($csv_data, $firstRowAsColumnNames);

        $propertiesMap = array();
        $map = array();
        /** @var \core_kernel_classes_Property $property */
        foreach($properties as $property){
            if(!in_array($property->getUri(), $this->getExludedProperties())){
                $propertiesMap[$property->getUri()] = $property->getLabel();
                if(($index = array_search($property->getLabel(), $header)) !== false){
                    $map[$property->getUri()] = $index;
                }
            }
        }
        $csvMap = array(
            'classProperties'   => $propertiesMap,
            'headerList'        => $header,
            'mapping'           => $map
        );

        return $csvMap;
    }

    public function getDataSample($file, $options = array(), $size = 5, $associative = true){
        $csv_data = new \tao_helpers_data_CsvFile($options);
        $csv_data->load($file);

        $count = min($size, $csv_data->count());
        $data = array();
        for($i = 0; $i < $count; $i++){
            $data[] = $csv_data->getRow($i, $associative);
        }
        return $data;
    }

}
