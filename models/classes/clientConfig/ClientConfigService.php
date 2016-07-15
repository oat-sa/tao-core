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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\model\clientConfig;

use oat\oatbox\service\ConfigurableService;
/**
 * 
 * @author Joel Bout
 */
class ClientConfigService extends ConfigurableService {

    const SERVICE_ID = 'tao/clientConfig';
    
    const OPTION_CONFIG_SOURCES = 'configs';
    
    /**
     * Returns an array of json serialisable content
     * to be send to the client json encoded 
     * 
     * @return array
     */
    public function getExtendedConfig() {
        $config = array();
        foreach ($this->getOption(self::OPTION_CONFIG_SOURCES) as $key => $source) {
            $config[$key] = $source->getConfig();
        }
        return $config;
    }
    
    /**
     * Either adds or overrides an existing client config
     * 
     * @param string $id
     * @param ClientConfig $configSource
     */
    public function setClientConfig($id, ClientConfig $configSource) {
        $sources = $this->hasOption(self::OPTION_CONFIG_SOURCES)
            ? $this->getOption(self::OPTION_CONFIG_SOURCES)
            : array()
        ;
        $sources[$id] = $configSource;
        $this->setOption(self::OPTION_CONFIG_SOURCES, $sources);
    }
}
