<?php

namespace oat\tao\model\security;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\metadata\exception\InconsistencyConfigException;

class SignatureGenerator extends ConfigurableService
{
    const SERVICE_ID = 'tao/SignatureGenerator';

    const OPTION_SALT = 'salt';

    /**
     * @param string[] $dataToHash
     *
     * @return string
     *
     * @throws InconsistencyConfigException
     */
    public function generate(...$dataToHash)
    {
        $salt = $this->getOption(self::OPTION_SALT);

        if (empty($salt)) {
            throw new InconsistencyConfigException('Salt is not defined');
        }

        $dataToCheck = json_encode($dataToHash);

        return hash('sha256', $salt . $dataToCheck . $salt);
    }
}
