<?php

namespace oat\tao\model\search;

use oat\oatbox\service\ConfigurableService;

class ResultSetMapper extends ConfigurableService
{
    public const SERVICE_ID = 'tao/ResultSetMapper';
    public const OPTION_STRUCTURE_MAP = 'optionStructureMap';

    public function map(string $structure)
    {
        $map = $this->getOption(self::OPTION_STRUCTURE_MAP);

        return $map[$structure] ?? $map['default'];
    }
}
