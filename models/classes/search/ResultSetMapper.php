<?php

namespace oat\tao\model\search;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;

/**
 * @deprecated Dynamic columns is managed as a feature for advanced search now.
 *             This class should not be used anymore
 */
class ResultSetMapper extends ConfigurableService
{
    /**
     * @deprecated Dynamic columns is managed as a feature for advanced search now.
     *             This class should not be used anymore
     */
    public const SERVICE_ID = 'tao/ResultSetMapper';

    /**
     * @deprecated Dynamic columns is managed as a feature for advanced search now.
     *             This class should not be used anymore
     */
    public const OPTION_STRUCTURE_MAP = 'optionStructureMap';

    /**
     * @deprecated Dynamic columns is managed as a feature for advanced search now.
     *             This class should not be used anymore
     */
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
