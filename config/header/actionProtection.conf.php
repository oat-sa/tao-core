<?php
/**
 * Configuration of the action protection service.
 * This service can be used to add protection to certain actions, for example by setting CSP headers
 *
 * Usage:
 * 'frameSourceWhitelist' => [
 *      "'self'",
 *      'example-domain.com',
 *      '*.wildcard-domain.com'
 *  ]
 *
 * This example will allow "self", "example-domain.com" and "*.wildcard-domain.com" to include tao in an iFrame.
 *
 * Notes:
 * - Keywords such as 'self' and 'none' must include single quotes
 * - If no whitelisted domains are supplied, 'none' will be set automatically
 *
 * @see \oat\oatbox\action\ActionProtector
 */
