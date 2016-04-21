<?php
namespace oat\tao\test;

include_once dirname(__FILE__) . '/../includes/raw_start.php';

use \common_ext_ExtensionsManager;
use \common_persistence_Manager;


abstract class RestTestRunner extends TaoPhpUnitTestRunner
{

    protected $host = ROOT_URL;

    protected $userUri = "";

    protected $login = "";

    protected $password = "";
    
    public function setUp()
    {
        TaoPhpUnitTestRunner::initTest();
        $this->disableCache();
        
        // creates a user using remote script from joel
        
        $testUserData = array(
            PROPERTY_USER_LOGIN => 'tjdoe',
            PROPERTY_USER_PASSWORD => 'test123',
            PROPERTY_USER_LASTNAME => 'Doe',
            PROPERTY_USER_FIRSTNAME => 'John',
            PROPERTY_USER_MAIL => 'jdoe@tao.lu',
            PROPERTY_USER_DEFLG => \tao_models_classes_LanguageService::singleton()->getLanguageByCode(DEFAULT_LANG)->getUri(),
            PROPERTY_USER_UILG => \tao_models_classes_LanguageService::singleton()->getLanguageByCode(DEFAULT_LANG)->getUri(),
            PROPERTY_USER_ROLES => array(
                INSTANCE_ROLE_GLOBALMANAGER
            )
        );
        
        $testUserData[PROPERTY_USER_PASSWORD] = 'test' . rand();
        
        $data = $testUserData;
        $data[PROPERTY_USER_PASSWORD] = \core_kernel_users_Service::getPasswordHash()->encrypt($data[PROPERTY_USER_PASSWORD]);
        $tmclass = new \core_kernel_classes_Class(CLASS_TAO_USER);
        $user = $tmclass->createInstanceWithProperties($data);
        \common_Logger::i('Created user ' . $user->getUri());
        
        // prepare a lookup table of languages and values
        $usage = new \core_kernel_classes_Resource(INSTANCE_LANGUAGE_USAGE_GUI);
        $propValue = new \core_kernel_classes_Property(RDF_VALUE);
        $langService = \tao_models_classes_LanguageService::singleton();
        
        $lookup = array();
        foreach ($langService->getAvailableLanguagesByUsage($usage) as $lang) {
            $lookup[$lang->getUri()] = (string) $lang->getUniquePropertyValue($propValue);
        }
        
        $data = array(
            'rootUrl' => ROOT_URL,
            'userUri' => $user->getUri(),
            'userData' => $testUserData,
            'lang' => $lookup
        );
        
        $this->login = $data['userData'][PROPERTY_USER_LOGIN];
        $this->password = $data['userData'][PROPERTY_USER_PASSWORD];
        $this->userUri = $data['userUri'];
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
     * @param
     *            returnType CURLINFO_HTTP_CODE, etc... (default returns rhe http response data
     *
     * @return mixed
     */
    protected function curl($url, $method = CURLOPT_HTTPGET, $returnType = "data", $curlopt_httpheaders = array())
    {
        $process = curl_init($url);
        if ($method != "DELETE") {
            curl_setopt($process, $method, 1);
        } else {
            curl_setopt($process, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        
        curl_setopt($process, CURLOPT_USERPWD, $this->login . ":" . $this->password);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        
        $headers = array_merge(array(
            "Accept: application/json"
        ), $curlopt_httpheaders);
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        if ($method == CURLOPT_POST) {
            curl_setopt($process, CURLOPT_POSTFIELDS, "");
        }
        // curl_setopt($process,CURLOPT_HTTPHEADER,$curlopt_httpheaders);
        $data = curl_exec($process);
        if ($returnType != "data") {
            $data = curl_getinfo($process, $returnType);
        }
        curl_close($process);
        return $data;
    }

}
