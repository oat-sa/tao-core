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
 * Copyright (c) 2013 (original work) (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */
namespace oat\tao\model\oauth\nonce;

use oat\oatbox\service\ConfigurableService;
/**
 * @author Joel Bout, <joel@taotesting.com>
 */
class KvNonceStore extends ConfigurableService implements NonceStore
{
    const OPTION_PERSISTENCE = 'persistence';
    const OPTION_TTL = 'ttl';
    
    const DEFAULT_TTL = 1800;
    
    const PREFIX = 'nonce_';
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\oauth\nonce\NonceStore::isValid()
     */
    public function isValid($id)
    {
        $ttl = $this->hasOption(self::OPTION_TTL) ? $this->getOption(self::OPTION_TTL) : self::DEFAULT_TTL;
        if ($this->getPersistence()->supportsFeature(\common_persistence_KeyValuePersistence::FEATURE_NX)) {
            return $this->getPersistence()->set(self::PREFIX.$id, 't', $ttl, true);
        } else {
            if ($this->getPersistence()->exists(self::PREFIX.$id)) {
                return false;
            }
            return $this->getPersistence()->set(self::PREFIX.$id, 't', $ttl);
        }
    }
    
    /**
     * @return \common_persistence_KeyValuePersistence
     */
    protected function getPersistence()
    {
        $pm = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID);
        return $pm->getPersistenceById($this->getOption(self::OPTION_PERSISTENCE));
    }
}