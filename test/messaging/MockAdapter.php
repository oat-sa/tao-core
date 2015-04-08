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

namespace oat\tao\test\messaging;

use oat\tao\model\messaging\Transport;
use oat\oatbox\Configurable;
use oat\tao\model\messaging\Message;
/**
 * An implementation that writes the messages to the filesystem
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 */
class MockAdapter extends Configurable implements Transport
{
    public $title;
    public $body;
    public $to;
    
    public function send(Message $message)
    {
        $this->title = $message->getTitle();
        $this->body = $message->getBody();
        $this->to = $message->getTo();
        return true;
    }
}
