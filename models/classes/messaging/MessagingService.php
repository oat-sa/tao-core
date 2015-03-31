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
class MessagingService extends \tao_models_classes_Service
{
    const CONFIG_KEY = 'messaging';
    
    private $errors = '';
    
    /**
     * @var Transport
     */
    private $transport = null;
    
    /**
     * Get the current transport implementation
     * 
     * @return Transport>
     */
    public function getTransport()
    {
        if (is_null($this->transport)) {
            $tao = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
            $transport = $tao->getConfig(self::CONFIG_KEY);
            if (!is_object($transport) || !$transport instanceof Transport) {
                throw new \common_exception_InconsistentData('Transport strategy not correctly set for '.__CLASS__);
            }
            $this->transport = $transport;
        }
        return $this->transport;
    }
    
    /**
     * Set the transport implementation to use
     * 
     * @param Transport $transporter
     */
    public function setTransport(Transport $transporter)
    {
        $this->transport = $transporter;
        $tao = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $tao->setConfig(self::CONFIG_KEY, $this->transport);
    }
    
    /**
     * Send a message (destination is part of the message)
     * 
     * @param Message $message
     * @return boolean
     */
    public function send(Message $message)
    {
<<<<<<< HEAD
        return $this->getTransport()->send($message);
=======
       $adapter = new MailAdapter($this->getConfig());
        $adapter->addMessage($message);
        $count = $adapter->send();
        if ($count === 0) {
            $this->errors = $adapter->getErrors();
        }
        return $count === 1;
>>>>>>> 677a951... Password reset form
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
    
<<<<<<< HEAD
=======
    /**
     * Get the detailed error message. Empty string if none.
     * @return string
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Get MailAdapter config
     * @return array
     */
    public function getConfig()
    {
        $tao = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        return $tao->getConfig(self::CONFIG_KEY);
    }
>>>>>>> 677a951... Password reset form
}
