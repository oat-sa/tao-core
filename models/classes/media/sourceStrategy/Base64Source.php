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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 * 
 */
namespace oat\tao\model\media\sourceStrategy;

use oat\tao\model\media\MediaBrowser;
/**
 * This media source gives access to files base 64 encoded
 */
class Base64Source implements MediaBrowser
{
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\media\MediaBrowser::getFileInfo()
     */
    public function getFileInfo($link)
    {
        throw new \common_Exception(__FUNCTION__.' not implemented');
    }

    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\media\MediaBrowser::download()
     */
    public function download($link)
    {
        throw new \common_Exception(__FUNCTION__.' not implemented');
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\media\MediaBrowser::getDirectory()
     */
    public function getDirectory($parentLink = '/', $acceptableMime = array(), $depth = 1)
    {
        throw new \common_Exception('Unable to browse the internet');
    }
}
