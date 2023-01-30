<?php

namespace oat\tao\model;

class EnvPhpSerializableFactory
{
    public function create(string $index): EnvPhpSerializable
    {
        if (strlen($index) < 1) {
            throw new \InvalidArgumentException('Empty index.');
        }
        return new EnvPhpSerializable($index);
    }
}
