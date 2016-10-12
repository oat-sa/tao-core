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

namespace oat\tao\model\metadata;

use oat\tao\model\metadata\exception\InconsistencyConfigException;
use oat\tao\model\metadata\reader\KeyReader;

/**
 * Class MetadataFactory
 * @package oat\tao\model\metadata\factory
 *
 */
class MetadataFactory
{
    const INJECTOR_READERS = 'source';
    const INJECTOR_WRITERS = 'destination';

    const INJECTOR_CLASS_NAME  = 'class';
    const INJECTOR_PARAMS_NAME = 'params';

    /**
     * Create injectors from $options e.g. config
     *
     * @param array $options
     * @return array
     */
    public function createInjectors(array $options)
    {
        $injectors = [];
        foreach ($options as $option) {
            $injectors[] = $this->createInjector($option);
        }
        return $injectors;
    }

    /**
     * Create an injector based on $options
     *
     * @param $option
     * @return mixed
     */
    public function createInjector(array $option)
    {
        $this->assertConfigFieldExists($option, self::INJECTOR_CLASS_NAME);
        $this->assertConfigFieldExists($option, self::INJECTOR_READERS);
        $this->assertConfigFieldExists($option, self::INJECTOR_WRITERS);

        $readers = $this->createReaders($option[self::INJECTOR_READERS]);
        $writers = $this->createWriters(self::INJECTOR_WRITERS, $option[self::INJECTOR_WRITERS]);

        return $this->createObjectFromClassName(
            self::INJECTOR_CLASS_NAME,
            $option[self::INJECTOR_CLASS_NAME],
            array(
                'readers' => $readers,
                'writers' => $writers
            )
        );
    }

    public function createReaders($configs)
    {
        $readers = [];
        foreach ($configs as $alias => $key) {
            $readers[] = new KeyReader($alias, $key);
        }
        return $readers;
    }

    /**
     * Create injector helpers e.g. reader/writer
     *
     * @param $writerName
     * @param $configs
     * @return array
     * @throws InconsistencyConfigException
     */
    protected function createWriters($writerName, $configs)
    {
        $writers = [];
        $this->assertIsValidArray($configs, $writerName);
        foreach ($configs as $name => $config) {

            if (! isset($config[self::INJECTOR_CLASS_NAME])) {
                throw new InconsistencyConfigException(
                    'Field "' . $writerName . '/' . $name . '" config has to contain a class field.'
                );
            }
            $className = $config[self::INJECTOR_CLASS_NAME];

            $params = [];
            try {
                $this->assertConfigFieldExists($config, self::INJECTOR_PARAMS_NAME);
                $this->assertIsValidArray(
                    $config[self::INJECTOR_PARAMS_NAME], $writerName . '/' . self::INJECTOR_PARAMS_NAME
                );
                $params = $config[self::INJECTOR_PARAMS_NAME];
            } catch (InconsistencyConfigException $e) {
                \common_Logger::i('Unable to load construct parameters for metadata reader : ' . $className);
            }

            $writers[] =$this->createObjectFromClassName(
                $writerName . '/' . $name . '/' . self::INJECTOR_CLASS_NAME, $className, $params
            );
        }
        return $writers;
    }

    /**
     * Create object from classname if class exists
     *
     * @param $fieldName
     * @param $className
     * @param array $params
     * @return mixed
     * @throws InconsistencyConfigException
     */
    protected function createObjectFromClassName($fieldName, $className, $params=[])
    {
        if (! class_exists($className)) {
            throw new InconsistencyConfigException('Injector field "' . $fieldName . '" is not a valid class.');
        }
        return new $className($params);
    }

    /**
     * Throws exception if field does not exist in $config array
     *
     * @param array $config
     * @param $field
     * @throws InconsistencyConfigException
     */
    protected function assertConfigFieldExists(array $config, $field)
    {
        if (! isset($config[$field])) {
            throw new InconsistencyConfigException('An injector config has to contain a "' . $field . '" field.');
        }
    }

    /**
     * Throws exception if field is not an array
     * Custom exception message with $fieldName
     *
     * @param $field
     * @param $fieldName
     * @throws InconsistencyConfigException
     */
    protected function assertIsValidArray($field, $fieldName)
    {
        if (! is_array($field)) {
            throw new InconsistencyConfigException('Injector config field "' . $fieldName . '" has to be an array.');
        }
        if (empty($field)) {
            throw new InconsistencyConfigException('Injector config field "' . $fieldName . '" cannot be empty.');
        }
    }

    /*public function getInjectors()
    {
        $config = [
            [
                'class' => ConcatenateFirstLastNameInjector::class,
                'source' => [
                    'name2' => [
                        'class' => CsvReader::class,
                        'params' => [
                            'field' => 5
                        ]
                    ],
                    'name1' => [
                        'class' => CsvReader::class,
                        'params' => [
                            'field' => 3
                        ]
                    ]
                ],
                'destination' => [
                    [
                        'type' => PropertyRegister::class,
                        'params' => [
                            'propertyName' => 'name',
                            'propertyUri' => 'http://abcd.tao',
                        ]
                    ]
                ]
            ]
        ];
    }*/

}