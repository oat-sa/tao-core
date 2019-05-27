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

use oat\tao\model\entryPoint\Entrypoint;
use oat\tao\model\entryPoint\EntryPointService;

class EntryPointServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntryPointService
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = require __DIR__ . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . 'entrypoint.conf.php';
    }

    public function testRemoveEntryPoint()
    {

        $options = $this->service->getOptions();
        $json = json_encode($options);

        self::assertEquals('{"existing":{"passwordreset":{},"dummy":{}},"postlogin":["passwordreset","dummy"],"prelogin":["passwordreset","dummy"],"new_tag":["passwordreset","dummy"]}',
            $json);

        $this->service->removeEntryPoint('passwordreset');

        $options = $this->service->getOptions();
        $json = json_encode($options);

        self::assertEquals('{"existing":{"dummy":{}},"postlogin":["dummy"],"prelogin":["dummy"],"new_tag":["dummy"]}',
            $json);
    }
}

class DummyEntryPoint implements Entrypoint
{
    public function getId()
    {
    }

    public function getTitle()
    {
    }

    public function getLabel()
    {
    }

    public function getDescription()
    {
    }

    public function getUrl()
    {
    }
}
