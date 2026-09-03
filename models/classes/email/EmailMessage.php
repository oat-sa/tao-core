<?php
declare(strict_types=1);

namespace oat\tao\model\email;

use oat\oatbox\user\User;

/**
 * @phpstan-consistent-constructor
 */
class EmailMessage
{
    private User $to;
    private string $subject;
    private string $bodyHtml;
    private string $bodyText;
    private string $from;
    /** @var string[] */
    private array $headers;

    /**
     * @param string[] $headers
     */
    public function __construct(
        User $to,
        string $subject,
        string $bodyHtml,
        string $bodyText,
        string $from,
        array $headers = []
    ) {
        $this->to = $to;
        $this->subject = $subject;
        $this->bodyHtml = $bodyHtml;
        $this->bodyText = $bodyText;
        $this->from = $from;
        $this->headers = $headers;
    }

    public function getTo(): User
    {
        return $this->to;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBodyHtml(): string
    {
        return $this->bodyHtml;
    }

    public function getBodyText(): string
    {
        return $this->bodyText;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
