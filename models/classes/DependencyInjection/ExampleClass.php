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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\DependencyInjection;

#
# @TODO This class will be removed. Just as sample.
#
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\filesystem\FileSystemService;

class ExampleClass
{
    /** @var FileSystemService */
    private $fileSystemService;

    /** @var PersistenceManager */
    private $persistenceManager;

    public function __construct(FileSystemService $fileSystemService, PersistenceManager $persistenceManager)
    {
        $this->fileSystemService = $fileSystemService;
        $this->persistenceManager = $persistenceManager;
    }

    public function test()
    {
        echo 'Worked!!';
        echo PHP_EOL;
        echo PHP_EOL;
        var_export(get_class($this->fileSystemService));
        echo PHP_EOL;
        var_export(get_class($this->persistenceManager));
    }
}
