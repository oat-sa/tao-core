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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\oauth;

use IMSGlobal\LTI\OAuth\OAuthConsumer;
use IMSGlobal\LTI\OAuth\OAuthToken;

/**
 * IMSGlobal lib doesn't define an interface for DataStore, but
 * has OAuthDataStore class instead. This interface duplicates it's requirements
 * @see \IMSGlobal\LTI\OAuth\OAuthDataStore
 */
interface ImsOauthDataStoreInterface
{
    /**
     * Returns the OauthConsumer for the specified key
     * @param string $consumer_key
     * @return OAuthConsumer
     */
    public function lookup_consumer($consumer_key);

    /**
     * Should verify if the token exists and return it
     * Always returns an token with an empty secret for now
     *
     * @param OAuthConsumer $consumer
     * @param string $token_type
     * @param string $token
     * @return OAuthToken
     */
    public function lookup_token($consumer, $token_type, $token);

    /**
     * Should verify if a nonce has already been used by specified consumer (got from lookup_consumer() call).
     * Should return false in case of acceptable nonce (which hasn't been used before)
     *
     * @param OAuthConsumer $consumer
     * @param string $token
     * @param string $nonce
     * @param string $timestamp
     * @return bool if nonce value exists
     */
    public function lookup_nonce($consumer, $token, $nonce, $timestamp);

    /**
     * Perform request_token request according to OAuth flow
     * Needed only for
     * @see \IMSGlobal\LTI\OAuth\OAuthServer::fetch_request_token()
     * call
     *
     * @param OAuthConsumer $consumer
     * @param callable|null $callback
     * @return mixed
     */
    public function new_request_token($consumer, $callback = null);

    /**
     * Perform access_token request according to OAuth flow
     * Needed only for
     * @see \IMSGlobal\LTI\OAuth\OAuthServer::fetch_access_token()
     * call
     *
     * @param string $token
     * @param OAuthConsumer $consumer
     * @param string $verifier Verification code
     * @return string
     */
    public function new_access_token($token, $consumer, $verifier = null);
}
