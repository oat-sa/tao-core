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

use oat\tao\model\passwordRecovery\PasswordRecoveryService;

/**
 * Controller provide actions to reset user password
 * 
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class tao_actions_PasswordRecovery extends tao_actions_CommonModule
{
    /**
     * @var oat\tao\model\passwordRecovery\PasswordRecoveryService 
     */
    private $passwordRecoveryService;
    
    /**
     * @var \tao_models_classes_UserService 
     */
    private $userService;
    
    /**
     * Constructor performs initializations actions
     */
    public function __construct()
    {
        //initialize user service
        $this->passwordRecoveryService = PasswordRecoveryService::singleton();
        $this->userService = \tao_models_classes_UserService::singleton();
        $this->defaultData();
    }
    
    /**
     * Show password recovery request form
     *
     * @author Aleh Hutnikau <hutnikau@1pt.com>
     * @return void
     */
    public function index() 
    {
        $formContainer = new tao_actions_form_PasswordRecovery();
        $form = $formContainer->getForm();
        
        if ($form->isSubmited() && $form->isValid()) {
            $mail = $form->getValue('userMail');
            $user = $this->passwordRecoveryService->getUser(PROPERTY_USER_MAIL, $mail);
            
            if ($user !== null) {
                \common_Logger::i("User requests a password (user URI: {$user->uriResource})");
                if ($this->passwordRecoveryService->sendMail($user)) {
                    $this->setData('recipientMail', $mail);
                    $this->setData('msg', __('An email has been sent.'));
                } else {
                    \common_Logger::w("Unsuccessful recovery password. {$this->passwordRecoveryService->getErrors()}.");
                    $this->setData('errorMessage', $this->passwordRecoveryService->getErrors());
                }
            } else {
                \common_Logger::i("Unsuccessful recovery password. Entered e-mail address: {$form->getValue('userMail')}.");
                $this->setData('recipientMail', $mail);
                $this->setData('msg', __('An email has been sent.'));
            }
        }
        
        $this->setData('form', $form->render());
        $this->setData('content-template', array('passwordRecovery/index.tpl', 'tao'));
        $this->setView('layout.tpl', 'tao');
    }
    
    /**
     * Password resrt form
     *
     * @author Aleh Hutnikau <hutnikau@1pt.com>
     * @return void
     */
    public function resetPassword()
    {
        $token = $this->getRequestParameter('token');
        
        $formContainer = new tao_actions_form_ResetUserPassword();
        $form = $formContainer->getForm();
        
        $form->setValues(array('token'=>$token));
        
        $user = $this->passwordRecoveryService->getUser(PasswordRecoveryService::PROPERTY_PASSWORD_RECOVERY_TOKEN, $token);
        if ($user === null) {
            \common_Logger::i("Password recovery token not found. Token value: {$token}");
            throw new Exception('This password reset link is no longer valid. It may have already been used. If you still wish to reset your password please request a new link.');
        }
        
        if ($form->isSubmited() && $form->isValid()) {
            $this->passwordRecoveryService->setPassword($user, $form->getValue('newpassword'));
            \common_Logger::i("User {$user->uriResource} has changed the password.");
            $this->setData('passwordChanged', true);
        }
        
        $this->setData('form', $form->render());
        $this->setData('content-template', array('passwordRecovery/password-reset.tpl', 'tao'));
        
        $this->setView('layout.tpl', 'tao');
    }
    
}
