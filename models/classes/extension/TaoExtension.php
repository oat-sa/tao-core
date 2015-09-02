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

use Composer\Package\PackageInterface;
use oatbox\extension\Extension;
use oatbox\composer\ExtensionInstaller;

class TaoExtension extends \common_ext_Extension implements Extension
{

    public function __construct(PackageInterface $package)
    {
        $ext = self::EXTENSION_NAME_KEY;
        $extra = $package->getExtra();
        if (!is_null($extra) && isset($extra[ExtensionInstaller::EXTENSION_NAME_KEY])) {
            parent::__construct($extra[ExtensionInstaller::EXTENSION_NAME_KEY]);
        } else {
            throw new \InvalidArgumentException('could not find extension name in manifest');
        };
    }
    
    public function runPostSrcInstall()
    {
        echo 'done';
        return true;
    }
}