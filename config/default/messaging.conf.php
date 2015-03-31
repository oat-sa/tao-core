<?php
return new oat\tao\model\messaging\transportStrategy\MailAdapter(
    array(
        'SMTPConfig' => array(
            'DEBUG_MODE' => false,
            'SMTP_HOST' => '127.0.0.1',
            'SMTP_PORT' => 25,
            'SMTP_AUTH' => false,
            'SMTP_USER' => '',
            'SMTP_PASS' => '',
        ),
        'defaultSender' => 'noreply@example.com'
    )
);
