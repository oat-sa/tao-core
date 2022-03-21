<?php

declare(strict_types=1);

namespace oat\tao\model\TestParamConverter;

class Query
{
    /** @var string */
    private $uri;

    /** @var array */
    private $listOfUris;

    /** @var SubQuery */
    private $subQuery;

    public function __construct(string $uri, array $listOfUris)
    {
        $this->uri = $uri;
        $this->listOfUris = $listOfUris;
        $this->subQuery = new SubQuery();
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getListOfUris(): array
    {
        return $this->listOfUris;
    }

    public function getSubQuery(): SubQuery
    {
        return $this->subQuery;
    }

    public function setSubQuery(SubQuery $subQuery): void
    {
        $this->subQuery = $subQuery;
    }
}
