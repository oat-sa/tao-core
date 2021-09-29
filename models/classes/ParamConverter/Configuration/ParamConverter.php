<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Configuration;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class ParamConverter extends ConfigurationAnnotation
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $class;

    /** @var array */
    private $options = [];

    /** @var bool */
    private $isOptional = false;

    /** @var string|null */
    private $converter;

    /**
     * @param string|array $data
     */
    public function __construct(
        $data = [],
        string $class = null,
        array $options = [],
        bool $isOptional = false,
        string $converter = null
    ) {
        $values = [];

        if (is_string($data)) {
            $values['value'] = $data;
        } else {
            $values = $data;
        }

        $values['class'] = $values['class'] ?? $class;
        $values['options'] = $values['options'] ?? $options;
        $values['isOptional'] = $values['isOptional'] ?? $isOptional;
        $values['converter'] = $values['converter'] ?? $converter;

        parent::__construct($values);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setValue(string $name): void
    {
        $this->setName($name);
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(?string $class): void
    {
        $this->class = $class;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function isOptional(): bool
    {
        return $this->isOptional;
    }

    public function setIsOptional(bool $isOptional): void
    {
        $this->isOptional = $isOptional;
    }

    public function getConverter(): ?string
    {
        return $this->converter;
    }

    public function setConverter(?string $converter): void
    {
        $this->converter = $converter;
    }
}
