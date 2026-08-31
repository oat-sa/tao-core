<?php
declare(strict_types=1);

namespace oat\tao\models\classes\email;

use oat\tao\models\classes\email\EmailAddressResolverInterface;
use oat\tao\models\classes\email\EmailMessage;
use oat\tao\models\classes\email\EmailSendResult;
use oat\tao\models\classes\email\EmailTransportInterface;

class EmailHandler implements EmailHandlerInterface
{
    private EmailTransportInterface $transport;
    private EmailAddressResolverInterface $addressResolver;

    public function __construct(
        EmailTransportInterface $transport,
        EmailAddressResolverInterface $addressResolver
    ) {
        $this->transport = $transport;
        $this->addressResolver = $addressResolver;
    }

    public function handle(EmailMessage $message): EmailSendResult
    {
        // Basic validation
        if (empty($message->getSubject()) || empty($message->getBodyHtml()) && empty($message->getBodyText())) {
            return new EmailSendResult(false, 'INVALID_MESSAGE');
        }

        // Resolve recipient email address
        $recipientEmail = $this->addressResolver->resolve($message->getTo());
        if ($recipientEmail === null) {
            return new EmailSendResult(false, 'NO_EMAIL');
        }

        // Check if transport is available
        if (!$this->transport->isAvailable()) {
            return new EmailSendResult(false, 'TRANSPORT_UNAVAILABLE');
        }

        // Attempt to send the email
        try {
            if ($this->transport->send($message)) {
                return new EmailSendResult(true, 'OK');
            } else {
                return new EmailSendResult(false, 'TRANSPORT_FAILED');
            }
        } catch (\Throwable $e) {
            // Log the exception for debugging purposes
            // In a real scenario, this would use a proper logger.
            error_log('Email transport failed: ' . $e->getMessage());
            return new EmailSendResult(false, 'TRANSPORT_FAILED');
        }
    }
}
