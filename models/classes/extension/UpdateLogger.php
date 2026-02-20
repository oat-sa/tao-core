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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\extension;

use oat\oatbox\service\ConfigurableService;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use oat\oatbox\filesystem\FileSystemService;

/**
 * Extends the generis updater to take into account
 * the translation files
 */
class UpdateLogger extends ConfigurableService implements LoggerInterface
{
    use LoggerTrait;

    public const SERVICE_ID = 'tao/updatelog';
    public const OPTION_FILESYSTEM = 'filesystem';

    /**
     * @param mixed $level
     * @param string|\Stringable $message
     * @param array $context
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $service = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
        $filesystem = $service->getFileSystem($this->getOption(self::OPTION_FILESYSTEM));

        $updateId = uniqid('update_' . time() . '_', true);
        $filesystem->write($updateId . '.log', $message);
    }
}
