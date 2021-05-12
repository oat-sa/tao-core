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
    public const FORM_TOKEN_NAMESPACE    = 'form_token';
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
        $this->invalidateExpiredAndSurplus($store->getAll());

        $token = new Token();
        $store->setToken($token->getValue(), $token);

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
        $token = $this->normaliseToken($token);
        $savedToken = $this->getStore()->getToken($token);

        return $this->isTokenValid($token, $savedToken);
    }

    /**
     * Check if form token is valid (does not revoke)
     *
     * @param string|Token $token The given token to validate
     *
     * @return boolean
     * @throws InvalidService
     */
    public function checkFormToken($token): bool
    {
        $token = $this->normaliseToken($token);
        $savedToken = $this->getStore()->getToken(self::FORM_TOKEN_NAMESPACE);

        return $this->isTokenValid($token, $savedToken);
    }

    /**
     * Check if the given token is valid and revoke it.
     *
     * @param string |Token $token
     *
     * @return bool Whether or not the token was successfully revoked
     *
     * @throws common_Exception
     * @throws common_exception_Unauthorized In case of an invalid token or missing
     */
    public function validateToken($token): bool
    {
        $token      = $this->normaliseToken($token);
        $storeToken = $this->getStore()->getToken($token);

        $result = $this->revokeToken($token);

        if ($storeToken === null || $this->isExpired($storeToken)) {
            throw new common_exception_Unauthorized();
        }

        return $result;
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
        $token = $this->normaliseToken($token);
        return $this->getStore()->removeToken($token);
    }

    /**
     * Invalidate the tokens in the pool :
     *  - remove the oldest if the pool raises it's size limit
     *  - remove the expired tokens
     *
     * @param Token[] $tokens
     *
     * @return array the invalidated pool
     *
     * @throws InvalidService
     */
    protected function invalidateExpiredAndSurplus(array $tokens): array
    {
        $timeLimit = $this->getTimeLimit();
        $poolSize = $this->getPoolSize();

        if ($timeLimit > 0) {
            foreach ($tokens as $key =>$token) {
                if ($this->isExpired($token)) {
                    $this->getStore()->removeToken($token->getValue());
                    unset($tokens[$key]);
                }
            }
        }

        if ($poolSize > 0 && count($tokens) >= $poolSize) {
            uasort($tokens, static function (Token $a, Token $b) {
                if ($a->getCreatedAt() === $b->getCreatedAt()) {
                    return 0;
                }
                return $a->getCreatedAt() < $b->getCreatedAt() ? -1 : 1;
            });

            //remove the elements at the beginning to fit the pool size
            for ($i = 0; $i <= count($tokens) - $poolSize; $i++) {
                $this->getStore()->removeToken($tokens[$i]->getValue());
            }
        }

        return $tokens;
    }

    /**
     * Get the configured pool size
     *
     * @param bool $withForm - Takes care of the form token
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

        if ($withForm && $poolSize > 0 && $this->getStore()->hasToken(self::FORM_TOKEN_NAMESPACE)) {
            $poolSize++;
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
        $tokens = $store->getAll();

        if ($this->getTimeLimit() > 0) {
            foreach ($tokens as $key => $token) {
                if ($this->isExpired($token)) {
                    $this->revokeToken($token->getValue());
                    unset($tokens[$key]);
                }
            }
        }

        $remainingPoolSize = $this->getPoolSize() - count($tokens);
        for ($i = 0; $i < $remainingPoolSize; $i++) {
            $newToken = new Token();
            $store->setToken($newToken->getValue(), $newToken);
            $tokens[] = $newToken;
        }

        return $tokens;
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
        $storedFormToken = $this->getStore()->getToken(self::FORM_TOKEN_NAMESPACE);;
        foreach ($tokenPool as $token) {
            if ($storedFormToken && $token->getValue() === $storedFormToken->getValue()) {
                // exclude form token from client configuration
                continue;
            }
            $jsTokenPool[] = $token->getValue();
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
        $this->getStore()->setToken(self::FORM_TOKEN_NAMESPACE, new Token());
    }

    /**
     * Get a token from the pool, which can be used for forms.
     * @return Token
     * @throws common_Exception
     */
    public function getFormToken(): Token
    {
        $formToken = $this->getStore()->getToken(self::FORM_TOKEN_NAMESPACE);

        if ($formToken === null) {
            $this->addFormToken();
            $formToken = $this->getStore()->getToken(self::FORM_TOKEN_NAMESPACE);
        }

        return $formToken;
    }

    private function getClientStore(): string
    {
        $store = $this->getOption(self::OPTION_CLIENT_STORE, self::DEFAULT_CLIENT_STORE);

        return in_array($store, self::CLIENT_STORE_OPTION_VALUES, true)
            ? $store
            : self::DEFAULT_CLIENT_STORE;
    }

    /**
     * @param string|Token $token
     * @return string
     */
    private function normaliseToken($token): string
    {
        if (is_object($token) && $token instanceof Token) {
            $token = $token->getValue();
        }

        return $token;
    }

    /**
     * @param string $tokenToCheck
     * @param Token|null $savedToken
     * @return bool
     */
    private function isTokenValid(string $tokenToCheck, ?Token $savedToken): bool
    {
        return $savedToken !== null && $savedToken->getValue() === $tokenToCheck && !$this->isExpired($savedToken);
    }
}
