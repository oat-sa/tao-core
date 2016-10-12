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

namespace oat\tao\model\metadata\injector;

use oat\tao\model\metadata\reader\Reader;
use oat\tao\model\metadata\writer\ontologyWriter\OntologyWriter;

abstract class OntologyMetadataInjector implements Injector
{
    /**
     * Components to read value from $dataSource
     *
     * @var Reader[]
     */
    protected $readers;

    /**
     * Components to write value to $resource
     *
     * @var OntologyWriter[]
     */
    protected $writers;

    /**
     * OntologyMetadataInjector constructor.
     * @param array $params Must contains keys 'readers' and 'writers'
     *
     * @throws \Exception
     */
    public function __construct(array $params)
    {
        if (empty($params['readers'])) {
            throw new \Exception();
        }
        if (empty($params['writers'])) {
            throw new \Exception();
        }

        $this->setReaders($params['readers']);
        $this->setWriters($params['writers']);
    }

    /**
     * Read all values from reader and store it into array of reader $name => reader $value
     *
     * @todo if no reader has read ? Throw specific exception ?
     *
     * @param array $dataSource
     * @return array All collected data from $this->readers
     */
    public function readValues(array $dataSource)
    {
        $data = [];
        foreach ($this->readers as $name => $reader) {
            $data[$name] = $reader->getValue($dataSource);
        }
        return $data;
    }

    /**
     * Write $data values using $this->writers
     *
     * @todo if no writer has wrote ? Throw specific exception ?
     *
     * @param \core_kernel_classes_Resource $resource
     * @param array $data
     */
    public function writeValues(\core_kernel_classes_Resource $resource, array $data)
    {
        $writers = [];
        $availableWriters = $this->writers;

        foreach ($availableWriters as $writer) {
            if ($writer->validate($data)) {
                $writers[] = $writer;
            }
        }
        foreach ($writers as $writer) {
            if ($writer instanceof OntologyWriter) {
                $writer->writeValue($resource, $data);
            }
        }
    }

    /**
     * Set $this->readers with Reader instance
     *
     * @param array $readers
     * @throws \Exception
     */
    protected function setReaders(array $readers)
    {
        foreach ($readers as $reader) {
            if (is_object($reader) && $reader instanceof Reader) {
                $this->readers[] = $reader;
            }
        }

        if (empty($this->readers)) {
            throw new \Exception();
        }
    }

    /**
     * Set $this->writers with OntologyWriter instance
     *
     * @param array $writers
     * @throws \Exception
     */
    protected function setWriters(array $writers)
    {
        foreach ($writers as $writer) {
            if (is_object($writer) && $writer instanceof OntologyWriter) {
                $this->writers[] = $writer;
            }
        }

        if (empty($this->writers)) {
            throw new \Exception();
        }
    }
}