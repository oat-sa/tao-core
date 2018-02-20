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

class TokenStoreKeyValue extends ConfigurableService implements TokenStore
{
    const OPTION_PERSISTENCE = 'persistence';

    const TOKENS_STORAGE_KEY = 'tao_tokens';

    /** @var common_persistence_KeyValuePersistence */
    private $persistence;

    /**
     * @return array|mixed
     */
    public function getTokens()
    {
        $value = $this->getPersistence()->get(static::TOKENS_STORAGE_KEY);
        $pool = (string)$value === '' ? [] : json_decode($value, true);

        return $pool;
    }

    /**
     * @param array $tokens
     * @throws \common_Exception
     */
    public function setTokens(array $tokens = [])
    {
        $this->getPersistence()->set(static::TOKENS_STORAGE_KEY, json_encode($tokens));
    }

    /**
     * @return bool
     */
    public function removeTokens()
    {
        return $this->getPersistence()->del(static::TOKENS_STORAGE_KEY);
    }

    /**
     * Get name/key of tokens
     * @throws \common_Exception
     */
    public function getTokenName()
    {
        $persistence = $this->getPersistence();

        if ($persistence->exists(static::TOKEN_NAME)) {
            $name = $persistence->get(self::TOKEN_NAME);
        } else {
            $name = 'tao_' . substr(md5(microtime()), rand(0, 25), 7);
            $persistence->set(self::TOKEN_NAME, $name);
        }

        return $name;
    }

    /**
     * @return common_persistence_KeyValuePersistence|\common_persistence_Persistence
     */
    protected function getPersistence()
    {
        if (is_null($this->persistence)) {
            $this->persistence = common_persistence_KeyValuePersistence::getPersistence($this->getOption(self::OPTION_PERSISTENCE));
        }
        return $this->persistence;
    }
}