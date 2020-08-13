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
 * Copyright (c) 2017-2020 (original work) Open Assessment Technologies SA ;
 */

declare(strict_types=1);

namespace oat\tao\model\security\xsrf;

use common_Exception;
use common_exception_Unauthorized;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\exception\InvalidService;
use oat\tao\model\security\TokenGenerator;
use PHPSession;

/**
 * This service let's you manage tokens to protect against XSRF.
 * The protection works using this workflow :
 *  1. Token pool gets generated and stored by front-end
 *  2. Front-end adds a token header using the token header "X-CSRF-Token"
 *  3. Back-end verifies the token using \oat\tao\model\security\xsrf\CsrfValidatorTrait
 *
 * @see \oat\tao\model\security\xsrf\CsrfValidatorTrait
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Martijn Swinkels <martijn@taotesting.com>
 */
class TokenService extends ConfigurableService
{
    use TokenGenerator;
    use LoggerAwareTrait;

    public const SERVICE_ID = 'tao/security-xsrf-token';

    // options keys
    public const POOL_SIZE_OPT       = 'poolSize';
    public const TIME_LIMIT_OPT      = 'timeLimit';
    public const VALIDATE_TOKENS_OPT = 'validateTokens';
    public const OPTION_STORE        = 'store';
    public const OPTION_CLIENT_STORE = 'clientStore';

    public const OPTION_CLIENT_STORE_LOCAL_STORAGE            = 'localStorage';
    public const OPTION_CLIENT_STORE_LOCAL_SESSION_STORAGE    = 'sessionStorage';
    public const OPTION_CLIENT_STORE_LOCAL_SESSION_INDEXED_DB = 'indexedDB';
    public const OPTION_CLIENT_STORE_MEMORY                   = 'memory';

    public const CLIENT_STORE_OPTION_VALUES = [
        self::OPTION_CLIENT_STORE_LOCAL_STORAGE,
        self::OPTION_CLIENT_STORE_LOCAL_SESSION_STORAGE,
        self::OPTION_CLIENT_STORE_LOCAL_SESSION_INDEXED_DB,
        self::OPTION_CLIENT_STORE_MEMORY,
    ];

    public const CSRF_TOKEN_HEADER       = 'X-CSRF-Token';
    public const FORM_POOL               = 'form_pool';
    public const JS_DATA_KEY             = 'tokenHandler';
    public const JS_TOKEN_KEY            = 'tokens';
    public const JS_TOKEN_POOL_SIZE_KEY  = 'maxSize';
    public const JS_TOKEN_TIME_LIMIT_KEY = 'tokenTimeLimit';
    public const JS_TOKEN_STORE          = 'store';

    private const DEFAULT_POOL_SIZE    = 6;
    private const DEFAULT_TIME_LIMIT   = 0;
    private const DEFAULT_CLIENT_STORE = self::OPTION_CLIENT_STORE_MEMORY;

    /**
     * Generates, stores and return a brand new token
     * Triggers the pool invalidation.
     *
     * @return Token
     * @throws common_Exception
     */
    public function createToken(): Token
    {
        $store = $this->getStore();
        $pool = $this->invalidate($store->getTokens());

        $token = new Token();
        $pool[] = $token;
        $store->setTokens($pool);

        return $token;
    }

    /**
     * Check if the given token is valid
     * (does not revoke)
     *
     * @param string|Token $token The given token to validate
     *
     * @return boolean
     * @throws InvalidService
     */
    public function checkToken($token): bool
    {
        $valid = false;
        $pool = $this->getStore()->getTokens();

        if (is_object($token) && $token instanceof Token) {
            $token = $token->getValue();
        }

        if ($pool !== null) {
            foreach ($pool as $savedToken) {
                if ($savedToken->getValue() === $token && !$this->isExpired($savedToken)) {
                    $valid = true;
                    break;
                }
            }
        }

        return $valid;
    }

    /**
     * Check if the given token is valid
     *
     * @param string |Token $token
     * @return boolean
     * @throws common_Exception
     * @throws common_exception_Unauthorized
     */
    public function validateToken($token): bool
    {
        $isValid = false;
        $expired = false;
        $pool = $this->getStore()->getTokens();

        if (is_object($token) && $token instanceof Token) {
            $token = $token->getValue();
        }

        if ($pool !== null) {
            foreach ($pool as $savedToken) {
                if ($savedToken->getValue() === $token) {
                    if ($this->isExpired($savedToken)) {
                        $expired = true;
                        break;
                    }
                    $isValid = true;
                    break;
                }
            }
        }

        if ($expired === true) {
            $this->revokeToken($token);
        }

        if (!$isValid) {
            throw new common_exception_Unauthorized();
        }

        return $this->revokeToken($token);
    }

    /**
     * Check if the given token has expired.
     *
     * @param Token $token
     * @return bool
     */
    private function isExpired(Token $token): bool
    {
        $expired = false;
        $actualTime = microtime(true);
        $timeLimit = $this->getTimeLimit();

        if (($timeLimit > 0) && $token->getCreatedAt() + $timeLimit < $actualTime) {
            $expired = true;
        }

        return $expired;
    }

    /**
     * Revokes the given token
     *
     * @param string|Token $token
     *
     * @return true
     *
     * @throws InvalidService
     */
    public function revokeToken($token): bool
    {
        $revoked = false;
        $store = $this->getStore();
        $pool = $store->getTokens();

        if (is_object($token) && $token instanceof Token) {
            $token = $token->getValue();
        }

        if ($pool !== null) {
            foreach ($pool as $key => $savedToken) {
                if ($savedToken->getValue() === $token) {
                    unset($pool[$key]);
                    $revoked = true;
                    break;
                }
            }
        }

        $store->setTokens($pool);

        return $revoked;
    }

    /**
     * Gets this session's name for token
     * @return string
     */
    public function getTokenName(): string
    {
        /** @var PHPSession $session */
        $session = PHPSession::singleton();

        if ($session->hasAttribute(TokenStore::TOKEN_NAME)) {
            $name = $session->getAttribute(TokenStore::TOKEN_NAME);
        } else {
            $name = 'tao_' . substr(md5(microtime()), mt_rand(0, 25), 7);
            $session->setAttribute(TokenStore::TOKEN_NAME, $name);
        }

        return $name;
    }

    /**
     * Invalidate the tokens in the pool :
     *  - remove the oldest if the pool raises it's size limit
     *  - remove the expired tokens
     *
     * @param Token[] $pool
     *
     * @return array the invalidated pool
     *
     * @throws InvalidService
     */
    protected function invalidate(array $pool): array
    {
        $actualTime = microtime(true);
        $timeLimit = $this->getTimeLimit();
        $poolSize = $this->getPoolSize();

        $reduced = array_filter($pool, static function (Token $token) use ($actualTime, $timeLimit) {
            if ($timeLimit > 0) {
                return $token->getCreatedAt() + $timeLimit > $actualTime;
            }
            return true;
        });

        if ($poolSize > 0 && count($reduced) > 0) {
            uasort($reduced, static function (Token $a, Token $b) {
                if ($a->getCreatedAt() === $b->getCreatedAt()) {
                    return 0;
                }
                return $a->getCreatedAt() < $b->getCreatedAt() ? -1 : 1;
            });

            //remove the elements at the beginning to fit the pool size
            while (count($reduced) >= $poolSize) {
                array_shift($reduced);
            }
        }

        return $reduced;
    }

    /**
     * Get the configured pool size
     *
     * @param bool $withForm - Takes care of the FORM_POOL
     *
     * @return int the pool size, 10 by default
     *
     * @throws InvalidService
     */
    public function getPoolSize(bool $withForm = true): int
    {
        $poolSize = self::DEFAULT_POOL_SIZE;
        if ($this->hasOption(self::POOL_SIZE_OPT)) {
            $poolSize = (int)$this->getOption(self::POOL_SIZE_OPT);
        }

        if ($withForm) {
            $store = $this->getStore();
            $pool = $store->getTokens();

            if ($poolSize > 0 && isset($pool[self::FORM_POOL])) {
                $poolSize++;
            }
        }
        return $poolSize;
    }

    /**
     * Get the configured time limit in seconds
     *
     * @return int the limit
     */
    protected function getTimeLimit(): int
    {
        $timeLimit = self::DEFAULT_TIME_LIMIT;
        if ($this->hasOption(self::TIME_LIMIT_OPT)) {
            $timeLimit = (int)$this->getOption(self::TIME_LIMIT_OPT);
        }
        return $timeLimit;
    }

    /**
     * Get the configured store
     *
     * @return TokenStore the store
     *
     * @throws InvalidService
     */
    protected function getStore(): TokenStore
    {
        $store = $this->getOption(self::OPTION_STORE);
        if (!$store instanceof TokenStore) {
            throw new InvalidService('Unexpected store for ' . __CLASS__);
        }
        return $this->propagate($store);
    }

    /**
     * Generate a token pool, and return it.
     *
     * @return Token[]
     * @throws common_Exception
     */
    public function generateTokenPool(): array
    {
        $store = $this->getStore();
        $pool = $store->getTokens();

        if ($this->getTimeLimit() > 0) {
            foreach ($pool as $token) {
                if ($this->isExpired($token)) {
                    $this->revokeToken($token->getValue());
                }
            }
        }

        $pool = $store->getTokens();
        $remainingPoolSize = $this->getPoolSize() - count($pool);

        for ($i = 0; $i < $remainingPoolSize; $i++) {
            $pool[] = new Token();
        }

        $store->setTokens($pool);

        return $pool;
    }

    /**
     * Gets the client configuration
     *
     * @return array
     *
     * @throws common_Exception
     */
    public function getClientConfig(): array
    {
        $tokenPool = $this->generateTokenPool();
        $jsTokenPool = [];
        foreach ($tokenPool as $key => $token) {
            if ($key !== self::FORM_POOL) {
                $jsTokenPool[] = $token->getValue();
            }
        }

        return [
            self::JS_TOKEN_TIME_LIMIT_KEY => $this->getTimeLimit() * 1000,
            self::JS_TOKEN_POOL_SIZE_KEY => $this->getPoolSize(false),
            self::JS_TOKEN_KEY => $jsTokenPool,
            self::VALIDATE_TOKENS_OPT => $this->getOption(self::VALIDATE_TOKENS_OPT),
            self::JS_TOKEN_STORE => $this->getClientStore(),
        ];
    }

    /**
     * Add a token that can be used for forms.
     * @throws common_Exception
     */
    public function addFormToken(): void
    {
        $store = $this->getStore();
        $tokenPool = $store->getTokens();

        $tokenPool[self::FORM_POOL] = new Token();

        $store->setTokens($tokenPool);
    }

    /**
     * Get a token from the pool, which can be used for forms.
     * @return Token
     * @throws common_Exception
     */
    public function getFormToken(): Token
    {
        $store = $this->getStore();
        $tokenPool = $store->getTokens();

        if (!isset($tokenPool[self::FORM_POOL])) {
            $this->addFormToken();
            $tokenPool = $store->getTokens();
        }

        return $tokenPool[self::FORM_POOL];
    }

    private function getClientStore(): string
    {
        $store = $this->getOption(self::OPTION_CLIENT_STORE, self::DEFAULT_CLIENT_STORE);

        return in_array($store, self::CLIENT_STORE_OPTION_VALUES, true)
            ? $store
            : self::DEFAULT_CLIENT_STORE;
    }
}
