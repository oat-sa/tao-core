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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */

namespace oat\tao\model\security\xsrf;

use common_persistence_KeyValuePersistence;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\session\SessionService;

/**
 * Class to store tokens in a key value storage
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class TokenStoreKeyValue extends ConfigurableService implements TokenStore
{

    const OPTION_PERSISTENCE = 'persistence';
    const TOKENS_STORAGE_KEY = 'tao_tokens';

    /**
     * @var common_persistence_KeyValuePersistence
     */
    private $persistence;

    /**
     * @return Token[]
     * @throws \common_exception_Error
     * @throws \common_Exception
     */
    public function getTokens()
    {
        $value = $this->getPersistence()->get($this->getKey());
        $storedTokens = json_decode($value, true) ?: [];
        $pool = [];

        foreach ($storedTokens as $key => $storedToken) {
            $pool[$key] = new Token($storedToken);
        }

        return $pool;
    }

    /**
     * @param Token[] $tokens
     * @throws \common_Exception
     */
    public function setTokens(array $tokens = [])
    {
        $this->getPersistence()->set($this->getKey(), json_encode($tokens));
    }

    /**
     * @return bool
     * @throws \common_exception_Error
     */
    public function removeTokens()
    {
        return $this->getPersistence()->del($this->getKey());
    }

    /**
     * @return common_persistence_KeyValuePersistence|\common_persistence_Persistence
     */
    protected function getPersistence()
    {
        if ($this->persistence === null) {
            $persistenceManager = $this->getServiceLocator()->get(\common_persistence_Manager::class);
            $this->persistence = $persistenceManager->getPersistenceById($this->getOption(self::OPTION_PERSISTENCE));
        }
        return $this->persistence;
    }

    /**
     * @return string
     * @throws \common_exception_Error
     */
    protected function getKey()
    {
        $user = $this->getServiceLocator()->get(SessionService::class)->getCurrentUser();
        return $user->getIdentifier() . '_' . static::TOKENS_STORAGE_KEY;
    }
}
