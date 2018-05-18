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

interface ImportServiceInterface
{
    /**
     * Launch the import of a csv file located at $filePath
     *
     * @param $file
     * @param array $extraProperties Rdf properties that need to be injected (role, FirstTimeInTao...)
     * @param array $options Contains technical options e.q. csvControls
     * @return \common_report_Report
     */
    public function import($file, $extraProperties = [], $options = []);

    /**
     * Get the mapper
     *
     * @return ImportMapperInterface|false
     */
    public function getMapper();

    /**
     * Set the mapper to use to map csv column to rdf properties
     *
     * @param ImportMapperInterface $importMapper
     * @return $this
     */
    public function setMapper(ImportMapperInterface $importMapper);
}