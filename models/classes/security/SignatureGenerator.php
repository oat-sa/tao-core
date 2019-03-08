<?php

namespace oat\tao\model\security;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\metadata\exception\InconsistencyConfigException;

class SignatureGenerator extends ConfigurableService
{
    const SERVICE_ID = 'tao/SignatureGenerator';

    const OPTION_SALT = 'salt';

    /**
     * @param string[] $dataToSign
     *
     * @return string
     *
     * @throws InconsistencyConfigException
     */
    public function generate(...$dataToSign)
    {
        $salt = $this->getOption(self::OPTION_SALT);

        if (empty($salt)) {
            throw new InconsistencyConfigException(sprintf('Option %s is not defined', self::OPTION_SALT));
        }

        $dataToCheck = json_encode($dataToSign);

        return hash('sha256', $salt . $dataToCheck . $salt);
    }
}
