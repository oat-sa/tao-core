<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Event;

use oat\tao\model\Context\ContextInterface;

class ParamConverterEvent implements Event
{
    /** @var ContextInterface */
    private $context;

    public function __construct(ContextInterface $context)
    {
        $this->context = $context;
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    public function getName(): string
    {
        return self::class;
    }
}
