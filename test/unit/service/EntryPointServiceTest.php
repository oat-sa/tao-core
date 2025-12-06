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
 * Copyright (c) 2017  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\tao\test\unit\service;

use PHPUnit\Framework\TestCase;
use oat\tao\model\entryPoint\EntryPointService;

class EntryPointServiceTest extends TestCase
{
    /**
     * @var EntryPointService
     */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = require __DIR__ . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR
            . 'entrypoint.conf.php';
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRemoveEntryPoint()
    {

        $options = $this->service->getOptions();
        $json = json_encode($options);

        self::assertEquals(
            '{"existing":{"passwordreset":{},"deliveryServer":{},"guestaccess":{},"proctoringDelivery":{}},'
                . '"postlogin":["deliveryServer","backoffice","proctoring","childOrganization","scoreReport","exam",'
                . '"testingLocationList","proctoringDelivery"],"prelogin":["guestaccess","proctoringDelivery"],'
                . '"new_tag":["proctoringDelivery"]}',
            $json
        );

        $this->service->removeEntryPoint('proctoringDelivery');

        $options = $this->service->getOptions();
        $json = json_encode($options);

        self::assertEquals(
            '{"existing":{"passwordreset":{},"deliveryServer":{},"guestaccess":{}},"postlogin":['
                . '"deliveryServer","backoffice","proctoring","childOrganization","scoreReport","exam",'
                . '"testingLocationList"],"prelogin":["guestaccess"],"new_tag":[]}',
            $json
        );
    }
}
