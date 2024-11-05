<?php

namespace oat\tao\model\IdentifierGenerator\Generator;

use core_kernel_classes_Resource;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;

class IdentifierGeneratorProxy implements IdentifierGeneratorInterface
{
    private Ontology $ontology;
    private array $idGenerators = [];

    public function __construct(Ontology $ontology)
    {
        $this->ontology = $ontology;
    }

    public function addIdentifierGenerator(IdentifierGeneratorInterface $idGenerator, string $resourceType): void
    {
        if (isset($this->idGenerators[$resourceType])) {
            throw new InvalidArgumentException('Id generator for type already defined');
        }

        $this->idGenerators[$resourceType] = $idGenerator;
    }

    public function generate(array $options = []): string
    {
        if (!isset($options[self::OPTION_RESOURCE]) && !isset($options[self::OPTION_RESOURCE_ID])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Option "%s" or "%s" is required to generate ID',
                    self::OPTION_RESOURCE,
                    self::OPTION_RESOURCE_ID
                )
            );
        }

        if (
            isset($options[self::OPTION_RESOURCE])
            && !$options[self::OPTION_RESOURCE] instanceof core_kernel_classes_Resource
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Option "%s" must be an instance of %s',
                    self::OPTION_RESOURCE,
                    core_kernel_classes_Resource::class
                )
            );
        }

        $resource = $options[self::OPTION_RESOURCE] ?? $this->ontology->getResource($options[self::OPTION_RESOURCE_ID]);
        $resourceType = $resource->getRootId();

        if (!isset($this->idGenerators[$resourceType])) {
            throw new InvalidArgumentException('ID generator for resource type not defined');
        }

        return $this->idGenerators[$resourceType]->generate($options);
    }
}
