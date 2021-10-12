<?php

/*
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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */
namespace oat\tao\install\utils\seed;

use InvalidArgumentException;
use ErrorException;
use oat\generis\model\GenerisRdf;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\Configurable;

class SeedParser
{
    public function fromFile($filePath): seed
    {
        if (!file_exists($filePath)) {
            throw new \ErrorException('Unable to find ' . $filePath);
        }
        $info = pathinfo($filePath);
        switch ($info['extension']) {
            case 'json':
                $parameters = json_decode(file_get_contents($filePath), true);
                if (is_null($parameters)) {
                    throw new InvalidArgumentException('Your JSON file is malformed');
                }
                break;
            case 'yml':
                if (extension_loaded('yaml')) {
                    $parameters = \yaml_parse_file($filePath);
                    if ($parameters === false) {
                        throw new InvalidArgumentException('Your YAML file is malformed');
                    }
                } else {
                    throw new ErrorException('Extension yaml should be installed');
                }
                break;
            default:
                throw new InvalidArgumentException('Please provide a JSON or YAML file');
        }
        return $this->fromArray($parameters);
    }

    public function fromArray($array): Seed
    {
        $options = $this->extractGlobal($array['configuration']['global']);
        $extensions = $array['extensions'];
        $services = [];
        foreach ($array['configuration'] as $extension => $configs) {
            foreach ($configs as $key => $config) {
                if ($this->isConfigurable($config)) {
                    $services[$extension . '/' . $key] = $this->unserialize($config);
                }
            }
        }
        $user = [
            GenerisRdf::PROPERTY_USER_LOGIN => $array['super-user']['login'],
            GenerisRdf::PROPERTY_USER_PASSWORD => $array['super-user']['password']
        ];
        $postInstallScripts = isset($array['postInstall']) ? $array['postInstall'] : [];
        return new Seed($options, $extensions, $services, $user, $postInstallScripts);
    }

    protected function unserialize($config)
    {
        $returnValue = null;
        if ($this->isConfigurable($config)) {
            $className = $config['class'];
            $params = isset($config['options']) ? $config['options'] : [];
            return new $className($this->unserialize($params));
        } elseif (is_array($config)) {
            $returnValue = [];
            foreach ($config as $key => $val) {
                $returnValue[$key] = $this->unserialize($val);
            }
        } else {
            $returnValue = $config;
        }
        return $returnValue;
    }

    protected function unserializeConfigurable($config): Configurable
    {
        if (!isset($config['class'])) {
            throw new InvalidSeedException('Configurable without class parameter');
        }
        $className = $config['class'];
        $params = isset($config['options']) ? $config['options'] : [];
        foreach ($params as $key => $value) {
            if ($this->isConfigurable($value)) {
                $params[$key] = $this->unserializeConfigurable($value);
            }
        }
        if (!is_a($className, Configurable::class, true)) {
            throw new InvalidSeedException($className . ' is not a Configurable');
        }
        return new $className($params);
    }

    protected function isConfigurable($config): bool
    {
        return isset($config['type']) && in_array($config['type'], ['configurableService', 'configurable'])
            && isset($config['class']) && is_a($config['class'], Configurable::class, true)
        ;
    }

    protected function setDefaultServices($services, $array): array
    {
        if (!isset($services[FileSystemService::SERVICE_ID])) {
            $services[FileSystemService::SERVICE_ID] = new FileSystemService([
                'adapters' => [
                    'default' => [
                        'class' => 'Local',
                        'options' => [
                            'root' => $array['configuration']['global']['file_path']
                        ]
                    ]
                ]
            ]);
        }
        return $services;
    }

    protected function extractGlobal($array): SeedOptions
    {
        $optional = [];
        if (isset($array['lang'])) {
            $optional[SeedOptions::OPTION_LANGUAGE] = $array['lang'];
        }
        if (isset($array['mode'])) {
            $optional[SeedOptions::OPTION_DEBUG] = $array['mode'] !== 'debug';
        }
        if (isset($array['instance_name'])) {
            $optional[SeedOptions::OPTION_INSTANCE_NAME] = $array['instance_name'];
        }
        if (isset($array['timezone'])) {
            $optional[SeedOptions::OPTION_TIMEZONE] = $array['timezone'];
        }
        if (isset($array['import_data'])) {
            $optional[SeedOptions::OPTION_INSTALL_SAMPLES] = $array['import_data'];
        }
        return new SeedOptions($array['url'], $array['namespace'], $optional);
    }
}
