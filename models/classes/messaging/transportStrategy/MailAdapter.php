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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */

namespace oat\tao\model\messaging\transportStrategy;

use oat\tao\model\messaging\Transport;
use oat\tao\model\messaging\transportStrategy\AbstractAdapter;
use oat\tao\model\messaging\Message;
use oat\oatbox\user\User;
use oat\oatbox\Configurable;

/**
 * MailAdapter sends email messages using PHPMailer. 
 * 
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 */
class MailAdapter extends Configurable implements Transport
{
    /**
     * PHPMailer instance
     * @see https://github.com/PHPMailer/PHPMailer
     * @access protected
     * @var PHPMailer
     */
    protected $mailer = null;
    
    protected $errors = '';
    
    /**
     * Initialize PHPMailer
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function __construct($config)
    {
        parent::__construct($config);
        
        $this->mailer = new \PHPMailer();
        
        $SMTPConfig = $this->getOption('SMTPConfig');
        
        $this->mailer->IsSMTP();
        $this->mailer->SMTPKeepAlive = true;
        $this->mailer->SMTPDebug = $SMTPConfig['DEBUG_MODE'];
        $this->mailer->SMTPAuth = $SMTPConfig['SMTP_AUTH'];
        $this->mailer->Host = $SMTPConfig['SMTP_HOST'];
        $this->mailer->Port = $SMTPConfig['SMTP_PORT'];
        $this->mailer->Username = $SMTPConfig['SMTP_USER'];
        $this->mailer->Password = $SMTPConfig['SMTP_PASS'];
    }

    /**
     * Sent email message
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param Message $message
     * @return boolean whether message was sent
     */
    public function send(Message $message)
    {
        $this->mailer->SetFrom($this->getFrom($message));
        $this->mailer->AddReplyTo($this->getFrom($message));
        $this->mailer->Subject = $message->getTitle();
        $this->mailer->AltBody = strip_tags(preg_replace("/<br.*>/i", "\n", $message->getBody()));
        $this->mailer->MsgHTML($message->getBody());
        $this->mailer->AddAddress($this->getUserMail($message->getTo()));

        try {
            if ($this->mailer->Send()) {
                $message->setStatus(\oat\tao\model\messaging\Message::STATUS_SENT);
                $result = true;
            }
            if ($this->mailer->IsError()) {
                \common_Logger::e($this->mailer->ErrorInfo);
                $this->errors = $this->mailer->ErrorInfo;
                $message->setStatus(\oat\tao\model\messaging\Message::STATUS_ERROR);
                $result = false;
            }
        } catch (phpmailerException $pe) {
            if (DEBUG_MODE) {
                print $pe;
            }
        }
        $this->mailer->ClearReplyTos();
        $this->mailer->ClearAllRecipients();
        $this->mailer->SmtpClose();

        return $result;
    }
    
    /**
     * @return string the error message. Empty string if none.
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Get user email address.
     * @param User $user
     * @return string
     * @throws Exception if email address is not valid
     */
    public function getUserMail(User $user)
    {
        $userMail = current($user->getPropertyValues(PROPERTY_USER_MAIL));
        
        if (!$userMail || !filter_var($userMail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('User email is not valid.');
        }
        
        return $userMail;
    }
    
    /**
     * Get a "From" address. If it was not specified for message then value will be retrieved from config.
     * @param Message $message (optional)
     * @return string
     */
    public function getFrom(Message $message = null) 
    {
        $from = $message === null ? null : $message->getFrom();
        if (!$from) {
            $from = $this->getOption('defaultSender');
        }
        return $from;
    }
}

?>