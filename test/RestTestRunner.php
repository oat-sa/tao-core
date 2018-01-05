<?php
namespace oat\tao\test;

include_once dirname(__FILE__) . '/../includes/raw_start.php';

use \common_ext_ExtensionsManager;
use \common_persistence_Manager;
use oat\generis\model\GenerisRdf;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\TaoRoles;


abstract class RestTestRunner extends TaoPhpUnitTestRunner
{

    protected $host = ROOT_URL;

    protected $userUri = "";

    protected $login = "";

    protected $password = "";
    
    protected function getUserData()
    {
        return array(
            GenerisRdf::PROPERTY_USER_LOGIN => 'tjdoe',
            GenerisRdf::PROPERTY_USER_PASSWORD => 'test123',
            GenerisRdf::PROPERTY_USER_LASTNAME => 'Doe',
            GenerisRdf::PROPERTY_USER_FIRSTNAME => 'John',
            GenerisRdf::PROPERTY_USER_MAIL => 'jdoe@tao.lu',
            GenerisRdf::PROPERTY_USER_DEFLG => \tao_models_classes_LanguageService::singleton()->getLanguageByCode(DEFAULT_LANG)->getUri(),
            GenerisRdf::PROPERTY_USER_UILG => \tao_models_classes_LanguageService::singleton()->getLanguageByCode(DEFAULT_LANG)->getUri(),
            GenerisRdf::PROPERTY_USER_PASSWORD => 'test' . rand(),
            GenerisRdf::PROPERTY_USER_ROLES => array(
                TaoRoles::GLOBAL_MANAGER
            )
        );
    }
    
    public function setUp()
    {
        TaoPhpUnitTestRunner::initTest();
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

    public function tearDown()
    {
        // removes the created user
        $user = new \core_kernel_classes_Resource($this->userUri);
        $success = $user->delete();
        $this->restoreCache();
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
    protected function curl($url, $method = CURLOPT_HTTPGET, $returnType = "data", $curlOptions = array())
    {
        $options = $this->getDefaultCurlOptions();
        if (!\tao_helpers_Array::isAssoc($curlOptions)) {
            $curlOptions = array(CURLOPT_HTTPHEADER => $curlOptions);
        }
        foreach ($curlOptions as $key => $value) {
            if (isset($options[$key]) && is_array($options[$key]) && is_array($value)) {
                $options[$key] = array_merge($options[$key], $value);
            } else {
                $options[$key] = $value;
            }
        }
        
        $process = curl_init($url);
        if ($method != "DELETE") {
            curl_setopt($process, $method, 1);
        } else {
            curl_setopt($process, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        
        curl_setopt_array($process, $options);
        
        $data = curl_exec($process);
        if ($returnType != "data") {
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
        return array(
            CURLOPT_USERPWD => $this->login . ":" . $this->password,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json"
            )
        );
    }

}
