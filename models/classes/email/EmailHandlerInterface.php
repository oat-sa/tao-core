<?php
declare(strict_types=1);

namespace oat\tao\model\email;

interface EmailHandlerInterface
{
    public function handle(EmailMessage $message): EmailSendResult;
}
