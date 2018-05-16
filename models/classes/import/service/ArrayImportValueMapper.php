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
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ConfigurableService;

class ArrayImportValueMapper extends ConfigurableService implements ImportValueMapperInterface
{
    use LoggerAwareTrait;

    const OPTION_DELIMITER = 'delimiter';

    const OPTION_VALUE_MAPPER = 'valueMapper';

    /** @var common_report_Report */
    protected $report;

    /**
     * @inheritdoc
     */
    public function map($value)
    {
        $mapValues   = [];
        $delimiter   = $this->getOption(static::OPTION_DELIMITER);
        $valueMapper = $this->getOption(static::OPTION_VALUE_MAPPER);
        $values      = explode($delimiter, $value);

        $this->report = common_report_Report::createInfo();

        foreach ($values as $value) {
            if ($valueMapper instanceof ImportValueMapperInterface) {
                $valueToBeMapped = $this->mapValueThroughMapper($valueMapper, $value);
                if (!is_null($valueToBeMapped)){
                    $mapValues[] = $valueToBeMapped;
                }
            }else{
                $mapValues[] = $value;
            }
        }

        return $mapValues;
    }

    /**
     * @inheritdoc
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param ImportValueMapperInterface $valueMapper
     * @param string $value
     * @return mixed
     * @throws \common_exception_Error
     */
    protected function mapValueThroughMapper($valueMapper, $value)
    {
        try {
            $returnValue = $valueMapper->map($value);
            $this->report->add($valueMapper->getReport());

            return $returnValue;
        } catch (RdsResourceNotFoundException $e) {
            $this->report->add(common_report_Report::createFailure($e->getMessage()));

            $this->logWarning($e->getMessage());
        }
    }
}