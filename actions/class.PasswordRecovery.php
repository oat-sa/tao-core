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
 * Copyright (c) 2015-2018 (original work) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\GenerisRdf;
use oat\tao\model\passwordRecovery\PasswordRecoveryService;
use oat\oatbox\log\LoggerAwareTrait;

/**
 * Controller provide actions to reset user password
 *
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class tao_actions_PasswordRecovery extends tao_actions_CommonModule
{
    use LoggerAwareTrait;

    /**
     * Show password recovery request form
     *
     * @author Aleh Hutnikau <hutnikau@1pt.com>
     * @return void
     */
    public function index()
    {
        $this->defaultData();
        $formContainer = new tao_actions_form_PasswordRecovery();
        $form = $formContainer->getForm();

        if ($form->isSubmited() && $form->isValid()) {
            $mail = $form->getValue('userMail');
            $user = $this->getPasswordRecovery()->getUser(GenerisRdf::PROPERTY_USER_MAIL, $mail);

            if ($user !== null) {
                $this->logInfo("User requests a password (user URI: {$user->getUri()})");
                $this->sendMessage($user);
            } else {
                $this->logInfo("Unsuccessful recovery password. Entered e-mail address: {$mail}.");
                $this->setData('header', __('An email has been sent'));
                $this->setData('info', __('A message with further instructions has been sent to your email address: %s', $mail));
            }
            $this->setData('content-template', array('passwordRecovery/password-recovery-info.tpl', 'tao'));
        } else {
            $this->setData('form', $form->render());
            $this->setData('content-template', array('passwordRecovery/index.tpl', 'tao'));
        }

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
        $this->defaultData();
        $token = $this->getRequestParameter('token');

        $formContainer = new tao_actions_form_ResetUserPassword();
        $form = $formContainer->getForm();

        $form->setValues(array('token'=>$token));

        $user = $this->getPasswordRecovery()->getUser(PasswordRecoveryService::PROPERTY_PASSWORD_RECOVERY_TOKEN, $token);
        if ($user === null) {
            $this->logInfo("Password recovery token not found. Token value: {$token}");
            $this->setData('header', __('User not found'));
            $this->setData('error', __('This password reset link is no longer valid. It may have already been used. If you still wish to reset your password please request a new link'));
            $this->setData('content-template', array('passwordRecovery/password-recovery-info.tpl', 'tao'));
        } else {
            if ($form->isSubmited() && $form->isValid()) {
                $this->getPasswordRecovery()->setPassword($user, $form->getValue('newpassword'));
                $this->logInfo("User {$user->getUri()} has changed the password.");
                $this->setData('info', __("Password successfully changed"));
                $this->setData('content-template', array('passwordRecovery/password-recovery-info.tpl', 'tao'));
            } else {
                $this->setData('form', $form->render());
                $this->setData('content-template', array('passwordRecovery/password-reset.tpl', 'tao'));
            }
        }

        $this->setView('layout.tpl', 'tao');
    }

    /**
     * Send message with password recovery instructions
     *
     * @author Aleh Hutnikau <hutnikau@1pt.com>
     * @param User $user
     * @return void
     */
    private function sendMessage(core_kernel_classes_Resource $user)
    {
        try {
            $messageSent = $this->getPasswordRecovery()->sendMail($user);
        } catch (Exception $e) {
            $messageSent = false;
            $this->logWarning("Unsuccessful recovery password. {$e->getMessage()}.");
        }

        if ($messageSent) {
            $mail = $this->getPasswordRecovery()->getUserMail($user);
            $this->setData('header', __('An email has been sent'));
            $this->setData('info', __('A message with further instructions has been sent to your email address: %s', $mail));
        } else {
            $this->setData('error', __('Unable to send the password reset request'));
        }
    }

    /**
     * @return PasswordRecoveryService
     */
    protected function getPasswordRecovery()
    {
        return PasswordRecoveryService::singleton();
    }
}
