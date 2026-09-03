<?php
declare(strict_types=1);

namespace oat\tao\model\email;


class EmailService
{
    private EmailHandlerInterface $emailHandler;

    public function __construct(EmailHandlerInterface $emailHandler)
    {
        $this->emailHandler = $emailHandler;
    }

    public function send(EmailMessage $message): EmailSendResult
    {
        return $this->emailHandler->handle($message);
    }

    /**
     * @return bool
     * @TODO: Implement a proper availability check through the handler or transport
     */
    public function isAvailable(): bool
    {
        // For now, we assume the service is available if the handler can be constructed.
        // A more sophisticated check might involve the underlying transport's availability.
        // This will be refined in later phases, possibly by checking the transport directly.
        return true;
    }
}
