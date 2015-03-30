<?php
/**
 * 
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
namespace oat\tao\model\messaging;

use oat\tao\model\messaging\transportStrategy\MailAdapter;
/**
 * Service to send messages to Tao Users
 * 
 * @author bout
 */
class MessagingService extends tao_models_classes_Service
{
    const CONFIG_KEY = 'messaging';
    
    /**
     * Send a message (destination is part of the message)
     * 
     * @param Message $message
     * @return boolean
     */
    public function send(Message $message)
    {
        $adapter = new MailAdapter();
        $adapter->addMessage($message);
        $count = $adapter->send();
        return $count === 1;
    }
    
    /**
     * Test if messaging is available
     * 
     * @return boolean
     */
    public function isAvailable()
    {
        $tao = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        return $tao->hasConfig(self::CONFIG_KEY);
    }
}
