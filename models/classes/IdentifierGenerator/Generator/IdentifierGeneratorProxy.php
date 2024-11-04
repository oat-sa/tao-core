<?php

namespace oat\tao\model\IdentifierGenerator\Generator;

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

        $resource = $options[self::OPTION_RESOURCE] ?? $this->ontology->getResource($options[self::OPTION_RESOURCE_ID]);
        $parentClasses = $resource->getParentClassesIds();
        $resourceType = array_pop($parentClasses);

        if (!isset($this->idGenerators[$resourceType])) {
            throw new InvalidArgumentException('ID generator for resource type not defined');
        }

        return $this->idGenerators[$resourceType]->generate($options);
    }
}
