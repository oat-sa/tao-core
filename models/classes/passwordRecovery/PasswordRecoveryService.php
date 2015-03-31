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

namespace oat\tao\model\passwordRecovery;

use oat\tao\helpers\Template;
use oat\tao\model\messaging\MessagingService;
use oat\tao\model\messaging\Message;

/**
 * Password recovery service
 *
 * @access public
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @package tao
 */
class PasswordRecoveryService extends \tao_models_classes_Service
{

    const PROPERTY_PASSWORD_RECOVERY_TOKEN = 'http://www.tao.lu/Ontologies/generis.rdf#passwordRecoveryToken';
    
    private $errors = ''; 

    /**
     * Send email message with password recovery instructions
     * 
     * @author Aleh Hutnikau <hutnikau@1pt.com>
     * @param core_kernel_classes_Resource $user The user has requested password recovery.
     * @return boolean Whether message was sent.
     */
    public function sendMail(\core_kernel_classes_Resource $user)
    {
        $messagingService = $this->getMessagingService();
        if (!$messagingService->isAvailable()) {
            throw new PasswordRecoveryException('Messaging service is not available.');
        }
        
        $userNameProperty = new \core_kernel_classes_Property(PROPERTY_USER_FIRSTNAME);
        $userMailProperty = new \core_kernel_classes_Property(PROPERTY_USER_MAIL);
        
        $userMail = (string) $user->getOnePropertyValue($userMailProperty);
        
        if (!filter_var($userMail, FILTER_VALIDATE_EMAIL)) {
            throw new PasswordRecoveryException('User email is not valid.');
        }
        
        $messageData = array(
            'user_name' => (string) $user->getOnePropertyValue($userNameProperty),
            'link' => $this->getPasswordRecoveryLink($user)
        );
        
        $message = new Message();
        $message->setTo($userMail);
        $message->setFrom('tao@test.com');
        $message->setBody($this->getMailContent($messageData));
        $message->setTitle(__("Your TAO Password"));
        
        $result = $messagingService->send($message);
        
        if (!$result) {
            $this->errors = $messagingService->getErrors();
        } 
        return $result;
    }
    
    /**
     * @return string the detailed error message (e.g. Error while sending the message). Empty string if none.
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Delete password recovery token.
     * 
     * @param \core_kernel_classes_Resource $user
     * @return boolean
     */
    public function deleteToken(\core_kernel_classes_Resource $user)
    {
        $tokenProperty = new \core_kernel_classes_Property(self::PROPERTY_PASSWORD_RECOVERY_TOKEN);
        return $user->removePropertyValues($tokenProperty);
    }
    
    /**
     * Function generates password recovery email message content
     * May be used in the following way:
     * <pre>
     * $this->getMailContent(array(
     *     'user_name'=>'John Doe',
     *     'link'=>$this->getPasswordRecoveryLink($user)
     * ));
     * </pre>
     * 
     * @author Aleh Hutnikau <hutnikau@1pt.com>
     * @param array $messageData
     * @return string Message content
     */
    private function getMailContent($messageData)
    {
        $renderer = new \Renderer();
        $renderer->setTemplate(Template::getTemplate('blocks/password-recovery-message.tpl', 'tao'));
        foreach ($messageData as $key => $value) {
            $renderer->setData($key, $value);
        }
        return $renderer->render();
    }

    /**
     * Get password recovery link.
     * 
     * @author Aleh Hutnikau <hutnikau@1pt.com>
     * @param core_kernel_classes_Resource $user The user has requested password recovery.
     * @return string Password recovery link.
     */
    private function getPasswordRecoveryLink(\core_kernel_classes_Resource $user)
    {
        $token = $this->generateRecoveryToken($user);
        return _url('resetPassword', 'Main', 'tao', array('token' => $token));
    }

    /**
     * Generate password recovery token. 
     * If user already has passwordRecoveryToken property then it will be replaced.
     * 
     * @author Aleh Hutnikau <hutnikau@1pt.com>
     * @param core_kernel_classes_Resource $user The user has requested password recovery.
     * @return string Password recovery token.
     */
    private function generateRecoveryToken(\core_kernel_classes_Resource $user)
    {
        $this->deleteToken($user);
        
        $token = md5(uniqid(mt_rand(), true));
        $tokenProperty = new \core_kernel_classes_Property(self::PROPERTY_PASSWORD_RECOVERY_TOKEN);
        $user->setPropertyValue($tokenProperty, $token);

        return $token;
    }
    
    private function getMessagingService()
    {
        return MessagingService::singleton();
    }
}

?>