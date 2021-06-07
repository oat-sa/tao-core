<?php

namespace oat\tao\model\search;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;

class ResultSetMapper extends ConfigurableService
{
    public const SERVICE_ID = 'tao/ResultSetMapper';
    public const OPTION_STRUCTURE_MAP = 'optionStructureMap';

    public function map(string $structure)
    {
        $map = $this->getOption(self::OPTION_STRUCTURE_MAP);

        if (!$this->getAdvancedSearchChecker()->isEnabled()) {
            return $map[$structure]['default'] ?? $map['default'];
        }
        return $map[$structure]['advanced'] ?? $map['default'];
    }

    private function getAdvancedSearchChecker(): AdvancedSearchChecker
    {
        return $this->getServiceLocator()->get(AdvancedSearchChecker::class);
    }
}
