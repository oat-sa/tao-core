<?php
declare(strict_types=1);

namespace oat\tao\models\classes\email;

interface EmailHandlerInterface
{
    public function handle(EmailMessage $message): EmailSendResult;
}
