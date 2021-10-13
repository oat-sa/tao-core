<?php

declare(strict_types=1);

namespace oat\tao\model\Serializer;

interface SerializerInterface
{
    /**
     * Serializes data in the appropriate format.
     *
     * @param mixed $data Any data
     * @param string $format Format name
     * @param array $context Options normalizers/encoders have access to
     */
    public function serialize($data, string $format, array $context = []): string;

    /**
     * Deserializes data into the given type.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function deserialize($data, string $type, string $format, array $context = []);
}
