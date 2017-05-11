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
 * Copyright (c) 2016-2017 (original work) Open Assessment Technologies SA ;
 */

namespace oat\tao\model\security;

/**
 * This traits let's you generate a random token
 *
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
trait TokenGenerator
{
    /**
     * Generates a security token
     * @param int $length the expected token length
     * @return string the token
     * @throws \common_Exception
     */
    protected function generate($length = 40)
    {
        try {
            return bin2hex(random_bytes($length / 2));
        } catch (\TypeError $e) {
            // This is okay, so long as `Error` is caught before `Exception`.
            throw new \common_Exception("An unexpected error has occurred while trying to generate a security token", 0, $e);
        } catch (\Error $e) {
            // This is required, if you do not need to do anything just rethrow.
            throw new \common_Exception("An unexpected error has occurred while trying to generate a security token", 0, $e);
        } catch (\Exception $e) {
            throw new \common_Exception("Could not generate a security token. Is our OS secure?", 0, $e);
        }
    }
}
