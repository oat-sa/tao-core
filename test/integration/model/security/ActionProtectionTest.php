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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 */

namespace oat\generis\test\integration\action;

use oat\generis\test\GenerisPhpUnitTestRunner;


/**
 * Test the protection of actions.
 */
class ActionProtectionTest extends GenerisPhpUnitTestRunner
{

    /**
     * @var string
     */
    private $cspHeader;

    /**
     * Retrieve the CSP header for checking
     * @param resource $curl
     * @param string $headerLine
     * @return int
     */
    public function handleHeaderLine($curl, $headerLine)
    {
        if ($this->cspHeader === null && strpos($headerLine, 'Content-Security-Policy') !== false) {
            $this->cspHeader = $headerLine;
        }
        return strlen($headerLine);
    }

    /**
     * Test the setting of CSP headers
     */
    public function testContentSecurityPolicyHeaders()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ROOT_URL);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'handleHeaderLine']);
        curl_exec($ch);
        curl_close($ch);

        static::assertNotNull($this->cspHeader, 'Content-Security-Policy header must be set.');
        static::assertContains('frame-ancestors', $this->cspHeader, 'The "frame-ancestors" directive must be set.');
    }

}