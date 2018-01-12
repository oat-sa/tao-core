<?php
namespace oat\tao\model\search\document;

interface Document
{
    public function __construct($id, $responseId, $index, $provider, $rootClass, $type = [], $body = []);
    public function getResponseIdentifier();
    public function getIdentifier();
    public function getBody();
    public function getType();
    public function getIndex();
    public function isValid();
    public function getProvider();
}