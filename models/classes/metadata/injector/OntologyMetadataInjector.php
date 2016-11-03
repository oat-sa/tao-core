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

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\metadata\exception\InconsistencyConfigException;
use oat\tao\model\metadata\exception\reader\MetadataReaderNotFoundException;
use oat\tao\model\metadata\exception\writer\MetadataWriterException;
use oat\tao\model\metadata\exception\injector\MetadataInjectorReadException;
use oat\tao\model\metadata\exception\injector\MetadataInjectorWriteException;
use oat\tao\model\metadata\reader\KeyReader;
use oat\tao\model\metadata\reader\Reader;
use oat\tao\model\metadata\writer\ontologyWriter\OntologyWriter;

/**
 * Class OntologyMetadataInjector
 *
 * @author Camille Moyon
 * @package oat\tao\model\metadata\injector
 */
class OntologyMetadataInjector extends ConfigurableService implements Injector
{
    const CONFIG_SOURCE = 'source';

    const CONFIG_DESTINATION = 'destination';

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
     * Override Configurable parent to check required field (source & destination)
     *
     * @param array $options
     * @throws InconsistencyConfigException
     */
    public function setOptions(array $options)
    {
        if (! array_key_exists(self::CONFIG_SOURCE, $options)
            || ! is_array($options[self::CONFIG_SOURCE])
            || empty($options[self::CONFIG_SOURCE])
        ) {
            throw new InconsistencyConfigException(__('Injector has to contains a valid "source" field.'));
        }

        if (! array_key_exists(self::CONFIG_DESTINATION, $options)
            || ! is_array($options[self::CONFIG_DESTINATION])
            || empty($options[self::CONFIG_DESTINATION])
        ) {
            throw new InconsistencyConfigException(__('Injector has to contains a valid "destination" field.'));
        }

        parent::setOptions($options);
    }

    /**
     * Create injector helpers (readers & writers) from options
     */
    public function createInjectorHelpers()
    {
        $this->setReaders($this->getOption(self::CONFIG_SOURCE));
        $this->setWriters($this->getOption(self::CONFIG_DESTINATION));
    }

    /**
     * Read all values from readers and store it into array of $name => reader $value
     * Throw exception if at least one reader cannot read value
     *
     * @param array $dataSource
     * @return array All collected data from $this->readers
     * @throws MetadataInjectorReadException
     */
    public function read(array $dataSource)
    {
        $data = $errors = [];

        foreach ($this->readers as $name => $reader) {
            try {
                $data[$name] = $reader->getValue($dataSource);
            } catch (MetadataReaderNotFoundException $e) {
                $errors[$name] = $e->getMessage();
            }
        }

        if (! empty($errors)) {
            foreach ($errors as $name => $error) {
                \common_Logger::d('Error on injector "' . __CLASS__ . '" with reader "' . $name . '" : ' . $error);
            }
            throw new MetadataInjectorReadException(
                'Injector "' . __CLASS__ . '" cannot read all required values from readers: ' . implode(', ', array_keys($errors))
            );
        }

        return $data;
    }

    /**
     * Write $data values using $this->writers
     *
     * @param \core_kernel_classes_Resource $resource
     * @param array $data
     * @param bool $dryrun
     * @return bool
     * @throws MetadataInjectorWriteException
     */
    public function write(\core_kernel_classes_Resource $resource, array $data, $dryrun = false)
    {
        $writers = $errors = [];

        foreach ($this->writers as $name => $writer) {
            try {
                $value = $writer->format($data);
                if ($writer->validate($value)) {
                    $writers[$name] = $writer;
                } else {
                    $errors[$name] = 'Writer "' . $name . '" cannot validate value.';
                }
            } catch (MetadataReaderNotFoundException $e) {
                $errors[$name] = 'Writer "' . $name . '" cannot format value: ' . $e->getMessage();
            }
        }

        foreach ($writers as $name => $writer) {
            if (! $writer instanceof OntologyWriter) {
                $errors[$name] = __CLASS__ . ' must implements ' . OntologyWriter::class;
                continue;
            }

            try {
                $writer->write($resource, $data, $dryrun);
            } catch (MetadataWriterException $e) {
                $errors[$name] = $e->getMessage();
            }
        }

        if (! empty($errors)) {
            foreach ($errors as $name => $error) {
                \common_Logger::d('Error on injector "' . __CLASS__ . '" with writer "' . $name . '" : ' . $error);
            }
            throw new MetadataInjectorWriteException(
                'Injector "' . __CLASS__ . '" cannot write values from writers: ' . implode(', ', array_keys($errors))
            );
        }

        return true;
    }

    /**
     * Set $this->readers with Reader instance
     *
     * @param array $readers
     * @throws InconsistencyConfigException
     */
    protected function setReaders(array $readers)
    {
        foreach ($readers as $name => $options) {
            $this->readers[$name] = new KeyReader($options);
        }
    }

    /**
     * Set $this->writers with OntologyWriter instance
     *
     * @param array $writers
     */
    protected function setWriters(array $writers)
    {
        foreach ($writers as $name => $destination) {
            $this->writers[$name] = $this->buildService($destination);
        }
    }

    /**
     * To configuration serialization
     *
     * @return string
     */
    public function __toPhpCode()
    {
        $source = '';
        if (! empty($this->readers)) {
            foreach ($this->readers as $reader) {
                $source .= \common_Utils::toHumanReadablePhpString($reader, 2) . PHP_EOL;
            }
        }

        $destination = '';
        if (! empty($this->writers)) {
            foreach ($this->writers as $writer) {
                $destination .= \common_Utils::toHumanReadablePhpString($writer, 2) . PHP_EOL;
            }
        }

        $params = [self::CONFIG_SOURCE => $this->readers, self::CONFIG_DESTINATION => $this->writers];

        return 'new ' . get_class($this) . '(' . \common_Utils::toHumanReadablePhpString($params, 1) . '),';
    }

}