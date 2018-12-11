<?php

namespace oat\tao\model\security;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\metadata\exception\InconsistencyConfigException;

class SignatureGenerator extends ConfigurableService
{
    const SERVICE_ID = 'tao/SignatureGenerator';

    const SALT = 'salt';

    /**
     * @param string[] $data
     *
     * @return string
     *
     * @throws InconsistencyConfigException
     */
    public function generate(...$data)
    {
        $salt = $this->getOption(self::SALT);

        if (empty($salt)) {
            throw new InconsistencyConfigException('Salt is not defined');
        }

        $dataToCheck = json_encode($data);

        return hash('sha256', $salt . $dataToCheck . $salt);
    }
}
