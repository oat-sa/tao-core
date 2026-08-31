<?php
declare(strict_types=1);

namespace oat\tao\models\classes\email;

interface EmailTransportInterface
{
    public function send(EmailMessage $message): bool;
    public function isAvailable(): bool;
}
