<?php

/**
 * Default config header created during install
 */

use oat\tao\model\oauth\lockout\NoLockout;
use oat\tao\model\oauth\OauthService;
use oat\tao\model\oauth\DataStore;
use oat\tao\model\oauth\nonce\NoNonce;

return new OauthService([
    'store' => new DataStore([
        'nonce' => new NoNonce()
    ]),
    'lockout' => new NoLockout()
]);
