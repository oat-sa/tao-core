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
 * Copyright (c) 2020 (original work) (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT)
 *
 */

namespace oat\tao\model\oauth\lockout;

use IMSGlobal\LTI\OAuth\OAuthRequest;

/**
 * Checks if the OAuth Session should be locked or not
 *
 * @package oat\tao\model\oauth\lockout
 */
interface LockoutInterface
{
    protected const REASON_INVALID_KEY = 'INVALID_KEY';
    protected const REASON_INVALID_SECRET = 'INVALID_SECRET';

    public const OPTION_THRESHOLD = 'threshold';
    public const OPTION_ = '';

    /**
     * Store the data about current session and failed attempts
     * to get possibility to analyze and make decision about locking
     * based on stored data
     *
     * @param OAuthRequest $request
     *
     * @return void
     */
    public function logFailedAttempt(OAuthRequest $request): void;

    /**
     * Checks if current session is allowed based on previous failed attempts
     *
     * @param OAuthRequest $request
     *
     * @return bool
     */
    public function isAllowed(OAuthRequest $request): bool;
}
