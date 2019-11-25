<?php
/**
 * Default config header created during install
 */
use oat\tao\model\oauth\DataStore;
use oat\tao\model\oauth\nonce\NoNonce;
use oat\tao\model\oauth\OauthService;

return new OauthService([
    'store' => new DataStore([
        'nonce' => new NoNonce()
    ])
]);
