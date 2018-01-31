<?php
/*  
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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

use oat\generis\Helper\SystemHelper;

/**
 * Utility class focusing  on the server environment.
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @deprecated
 */
class tao_helpers_Environment
{

    /**
     * Returns the maximum size for fileuploads in bytes.
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @deprecated use SystemHelper::getFileUploadLimit()
     * @return int The upload file limit.
     */
    public static function getFileUploadLimit()
    {
        return SystemHelper::getFileUploadLimit();
    }

    /**
     * Returns the Operating System running TAO as a String.
     * 
     * The returned value can be AFAIK:
     * 
     * * 'WINNT' (Win XP, Windows NT, ...)
     * * 'Linux'
     * * 'FreeBSD'
     * * 'Darwin' (Mac OS X)
     * * 'CYGWIN_NT-5.1'
     * * 'HP-UX'
     * * 'IRIX64'
     * * 'NetBSD'
     * * 'OpenBSD'
     * * 'SunOS'
     * * 'Unix'
     * * 'WIN32'
     * * 'Windows'
     *
     * @author Joel Bout, <joel@taotesting.com>
     * @deprecated use SystemHelper::getOperatingSystem
     * @return string The operating system that runs the script.
     */
    public static function getOperatingSystem()
    {
        return SystemHelper::getOperatingSystem();
    }
}
