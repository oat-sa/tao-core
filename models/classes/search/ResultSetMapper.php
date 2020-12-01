<?php

namespace oat\tao\model\search;

use oat\oatbox\service\ConfigurableService;

class ResultSetMapper extends ConfigurableService
{
    public const SERVICE_ID = 'tao/ResultSetMapper';
    public const OPTION_STRUCTURE_MAP = 'optionStructureMap';

    public function getPromiseModel(string $structure)
    {
        $map = $this->getOption(self::OPTION_STRUCTURE_MAP);
        if (isset($map[$structure])) {
            return $map[$structure];
        }

        return $map['default'];
    }

    public function getResultSetModel(array $content, string $structure)
    {
        $map = $this->getOption(self::OPTION_STRUCTURE_MAP);
        $allowedKeys = $map[$structure] ?? $map['default'];

        return array_filter($content, function ($key) use ($allowedKeys) {
            return $key === 'id' || in_array($key, array_keys($allowedKeys));
        }, ARRAY_FILTER_USE_KEY);
    }
}
