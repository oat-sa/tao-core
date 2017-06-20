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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\state;

use oat\oatbox\filesystem\FileSystem;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\exception\InvalidService;
use oat\oatbox\service\ConfigurableService;

/**
 * Persistence for the item delivery service
 *
 * @access public
 * @author Antoine Robin Bout, <antoine@taotesting.com>
 * @package tao
 */
class StateMigration
    extends ConfigurableService
{
    const SERVICE_ID = 'tao/migrationState';

    const OPTION_FILESYSTEM = 'fileSystem';

    /**
     * @var FileSystem
     */
    private $fileSystem = null;

    public function __construct(array $options = array())
    {
        if (!isset($options[self::OPTION_FILESYSTEM])) {
            throw new InvalidService(__("missing config %s for the service %s", self::OPTION_FILESYSTEM, self::class));
        }
        parent::__construct($options);
    }

    /**
     * Store the state of the service call
     *
     * @param string $userId
     * @param string $callId
     * @return boolean
     */
    public function archive($userId, $callId)
    {

        /** @var StateStorage $stateStorage */
        $stateStorage = $this->getServiceManager()->get(StateStorage::SERVICE_ID);

        $state = $stateStorage->get($userId, $callId);

        return (!is_null($state)) ? $this->getFileSystem()->write($this->generateSerial($userId, $callId), $state) : false;
    }

    public function restore($userId, $callId)
    {

        $state = $this->getFileSystem()->read($this->generateSerial($userId, $callId));

        /** @var StateStorage $stateStorage */
        $stateStorage = $this->getServiceManager()->get(StateStorage::SERVICE_ID);

        return $stateStorage->set($userId, $callId, $state);

    }

    public function removeState($userId, $callId)
    {
        /** @var StateStorage $stateStorage */
        $stateStorage = $this->getServiceManager()->get(StateStorage::SERVICE_ID);

        $stateStorage->del($userId, $callId);

    }

    public function removeBackup($userId, $callId)
    {

        return $this->getFileSystem()->delete($this->generateSerial($userId, $callId));
    }

    private function generateSerial($userId, $callId)
    {
        return md5($userId . $callId);
    }

    /**
     *
     * @return FileSystem
     */
    private function getFileSystem()
    {
        if (is_null($this->fileSystem)) {
            /** @var FileSystemService $fileSystemService */
            $fileSystemService = $this->getServiceManager()->get(FileSystemService::SERVICE_ID);
            $this->fileSystem = $fileSystemService->getFileSystem($this->getOption(self::OPTION_FILESYSTEM));

        }

        return $this->fileSystem;
    }

}