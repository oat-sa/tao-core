<?php

declare(strict_types=1);

namespace oat\tao\test\integration;

use oat\generis\model\GenerisRdf;
use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\TaoRoles;

abstract class RestTestRunner extends GenerisPhpUnitTestRunner
{
    protected $host = '';

    protected $userUri = '';

    protected $login = '';

    protected $password = '';

    public function setUp(): void
    {
        parent::setUp();

        $this->host = ROOT_URL;
        $this->disableCache();

        // creates a user using remote script from joel
        $userdata = $this->getUserData();
        $password = $userdata[GenerisRdf::PROPERTY_USER_PASSWORD];
        $userdata[GenerisRdf::PROPERTY_USER_PASSWORD] = \core_kernel_users_Service::getPasswordHash()->encrypt($userdata[GenerisRdf::PROPERTY_USER_PASSWORD]);
        $tmclass = new \core_kernel_classes_Class(TaoOntology::CLASS_URI_TAO_USER);
        $user = $tmclass->createInstanceWithProperties($userdata);
        \common_Logger::i('Created user ' . $user->getUri());

        $this->login = $userdata[GenerisRdf::PROPERTY_USER_LOGIN];
        $this->password = $password;
        $this->userUri = $user->getUri();
    }

    public function tearDown(): void
    {
        // removes the created user
        $user = new \core_kernel_classes_Resource($this->userUri);
        $success = $user->delete();
        $this->restoreCache();
    }

    protected function getUserData()
    {
        return [
            GenerisRdf::PROPERTY_USER_LOGIN => 'tjdoe',
            GenerisRdf::PROPERTY_USER_PASSWORD => 'test123',
            GenerisRdf::PROPERTY_USER_LASTNAME => 'Doe',
            GenerisRdf::PROPERTY_USER_FIRSTNAME => 'John',
            GenerisRdf::PROPERTY_USER_MAIL => 'jdoe@tao.lu',
            GenerisRdf::PROPERTY_USER_UILG => \tao_models_classes_LanguageService::singleton()->getLanguageByCode(DEFAULT_LANG)->getUri(),
            GenerisRdf::PROPERTY_USER_PASSWORD => 'test' . random_int(0, getrandmax()),
            GenerisRdf::PROPERTY_USER_ROLES => [
                TaoRoles::GLOBAL_MANAGER,
            ],
        ];
    }

    /**
     * shall be used beyond high level http connections unit tests (default parameters)
     *
     * @param string $url url to call
     * @param string $method
     * @param string $returnType CURLINFO_HTTP_CODE, etc... (default returns rhe http response data
     * @param array $curlOptions (numeric arrays get interpreted as headers)
     * @return mixed
     */
    protected function curl($url, $method = CURLOPT_HTTPGET, $returnType = 'data', $curlOptions = [])
    {
        $options = $this->getDefaultCurlOptions();
        if (! \tao_helpers_Array::isAssoc($curlOptions)) {
            $curlOptions = [CURLOPT_HTTPHEADER => $curlOptions];
        }
        foreach ($curlOptions as $key => $value) {
            if (isset($options[$key]) && is_array($options[$key]) && is_array($value)) {
                $options[$key] = array_merge($options[$key], $value);
            } else {
                $options[$key] = $value;
            }
        }

        $process = curl_init($url);
        if ($method !== 'DELETE') {
            curl_setopt($process, $method, 1);
        } else {
            curl_setopt($process, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        curl_setopt_array($process, $options);

        $data = curl_exec($process);
        if ($returnType !== 'data') {
            $data = curl_getinfo($process, $returnType);
        }
        curl_close($process);
        return $data;
    }

    /**
     * Default curl options used for e very call
     *
     * @return array
     */
    protected function getDefaultCurlOptions()
    {
        return [
            CURLOPT_USERPWD => $this->login . ':' . $this->password,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ];
    }

    protected function disableCache(): void
    {
        // just to avoid stopping tests with error "Call to undefined method disableCache()"
    }

    protected function restoreCache(): void
    {
        // just to avoid stopping tests with error "Call to undefined method restoreCache()"
    }
}
