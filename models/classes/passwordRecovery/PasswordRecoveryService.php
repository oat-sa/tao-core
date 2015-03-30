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

    /**
     * Send email message with password recovery instructions
     * 
     * @param core_kernel_classes_Resource $user The user has requested password recovery.
     * @return boolean Whether message was sent.
     */
    public function sendMail(\core_kernel_classes_Resource $user)
    {
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
        
        $message = new \tao_helpers_transfert_Message();
        $message->setTo($userMailProperty);
        $message->setBody($this->getMailContent($messageData));
        $message->setTitle(__("Your TAO Password"));
        
        $mailAdapter = $this->getMailAdapter();
        
        return $mailAdapter->send() === 1;
    }
    
    public function getErrors()
    {
        return 'error';
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
     * @param core_kernel_classes_Resource $user The user has requested password recovery.
     * @return string Password recovery token.
     */
    private function generateRecoveryToken(\core_kernel_classes_Resource $user)
    {
        $token = md5(uniqid(mt_rand(), true));

        $userNameProperty = new \core_kernel_classes_Property(self::PROPERTY_PASSWORD_RECOVERY_TOKEN);
        $user->removePropertyValues($userNameProperty);
        $user->setPropertyValue($userNameProperty, $token);

        return $token;
    }
    
    private function getMailAdapter()
    {
        return new \tao_helpers_transfert_MailAdapter();
    }
    
}

?>