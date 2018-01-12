<?php

namespace oat\tao\model\search\document;

class IndexDocument implements Document
{
    protected $id;
    protected $body;
    protected $index;
    protected $type;
    protected $provider;
    protected $responseId;

    public function __construct($id, $responseId, $index, $provider, $rootClass, $type = [], $body = [])
    {
        $this->id = $id;
        $this->responseId = $responseId;
        $this->provider = $provider;
        $this->index = $index;
        $this->type = $type;
        $body['provider'] = $provider;
        $body['rootClass'] = $rootClass;
        $this->body = $body;
    }

    public function getIdentifier()
    {
        return $this->id;
    }

    public function getResponseIdentifier()
    {
        return $this->responseId;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isValid()
    {
        // TODO: Implement isValid() method.
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function getIndex()
    {
        return $this->index;
    }
}
