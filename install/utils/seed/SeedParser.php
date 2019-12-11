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
use oat\oatbox\service\ConfigurableService;
use oat\generis\model\GenerisRdf;
use oat\oatbox\filesystem\FileSystemService;

class SeedParser
{
    public function fromFile($filePath) {
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

    public function fromArray($array) {
        $options = $this->extractGlobal($array['configuration']['global']);
        $extensions = $array['extensions'];
        $services = [];
        foreach($array['configuration'] as $extension => $configs) {
            foreach($configs as $key => $config) {
                if(isset($config['type']) && $config['type'] === 'configurableService'){
                    $className = $config['class'];
                    $params = $config['options'];
                    if (is_a($className, ConfigurableService::class, true)) {
                        $services[$extension.'/'.$key] = new $className($params);
                    }
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
    
    protected function setDefaultServices($services, $array) {
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
    
    protected function extractGlobal($array) {
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