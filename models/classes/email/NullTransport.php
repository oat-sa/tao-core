<?php
declare(strict_types=1);

namespace oat\tao\models\classes\email;

class NullTransport implements EmailTransportInterface
{
    public function send(EmailMessage $message): bool
    {
        // In a NullTransport, we just pretend to send the email
        // and always return true to indicate it was "handled".
        return true;
    }

    public function isAvailable(): bool
    {
        // A NullTransport is always available
        return true;
    }
}
