<?php
/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class tao_actions_Api extends tao_actions_CommonModule {

	/**
	 * @var tao_models_classes_UserService
	 */
	protected $userService;
	
	/**
	 * Name of the variable used for the execution environment 
	 * @var string
	 */
	const ENV_VAR_NAME = 'taoEnv';
	
	/**
	 * Constructor
	 * initialize the user service
	 */
	public function __construct(){
		
		parent::__construct();
		
		$this->userService = tao_models_classes_UserService::singleton();
	}
	
	/**
	 * create a unique token that will be exchanged during the communications
	 * @return string the token
	 */
	protected function createToken(){
		//get the sum of a unique token to identify the content
		return sha1( uniqid(self::ENV_VAR_NAME, true) );		//the env var is just used as a SALT
	}
	
	/**
	 * Build and load the item execution environment.
	 * 
	 * @param core_kernel_classes_Resource $processExecution
	 * @param core_kernel_classes_Resource $item
	 * @param core_kernel_classes_Resource $test
	 * @param core_kernel_classes_Resource $delivery
	 * @param core_kernel_classes_Resource $user
	 * 
	 * @return array
	 */
	protected function createExecutionEnvironment(core_kernel_classes_Resource $processExecution, 
													core_kernel_classes_Resource $item, 
													core_kernel_classes_Resource $test, 
													core_kernel_classes_Resource $delivery, 
													core_kernel_classes_Resource $user){
		$executionEnvironment = array();
		
		foreach(func_get_args() as $arg){
			if(is_null($arg)){
				return $executionEnvironment;
			}
		}
		
		//we build the data to give to the item
		$executionEnvironment = array(

			'token'			=> $this->createToken(),
			'localNamespace' => core_kernel_classes_Session::singleton()->getNameSpace(),
		
			CLASS_PROCESS_EXECUTIONS => array(
				'uri'		=> $processExecution->uriResource,
				RDFS_LABEL	=> $processExecution->getLabel()
			),
			
			TAO_ITEM_CLASS	=> array(
				'uri'		=> $item->uriResource,
				RDFS_LABEL	=> $item->getLabel()
			),
			TAO_TEST_CLASS	=> array(
				'uri'		=> $test->uriResource,
				RDFS_LABEL	=> $test->getLabel()
			),
			TAO_DELIVERY_CLASS	=> array(
				'uri'		=> $delivery->uriResource,
				RDFS_LABEL	=> $delivery->getLabel()
			),
			TAO_SUBJECT_CLASS => array(
				'uri'					=> $user->uriResource,
				RDFS_LABEL				=> $user->getLabel(),
				PROPERTY_USER_LOGIN		=> (string)$user->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_LOGIN)),
				PROPERTY_USER_FIRTNAME	=> (string)$user->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_FIRTNAME)),
				PROPERTY_USER_LASTNAME	=> (string)$user->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_LASTNAME))
			)
		);
		
		Session::setAttribute(self::ENV_VAR_NAME.'_'.tao_helpers_Uri::encode($user->uriResource), $executionEnvironment);
		
		return $executionEnvironment;
	} 
	
	/**
	 * create an execution environnement only for the authentication
	 * @return array the executionEnvironment
	 */
	public static function createAuthEnvironment(){
		
		$context = Context::getInstance();
		if(strtolower($context->getActionName()) == 'createauthenvironment'){
			throw new Exception('Action denied, only servers side call are allowed');
		}
		if(!Session::hasAttribute('processUri')){
			throw new Exception('Envirnoment can be create only in a workflow context');
		}
		$processExecution = new core_kernel_classes_Resource(tao_helpers_Uri::decode(Session::getAttribute('processUri')));
		
		$userService = tao_models_classes_UserService::singleton();
		$user = $userService->getCurrentUser();
		if(is_null($user)){
			throw new Exception('No user is logged in');
		}
		
		$sessionKey = self::ENV_VAR_NAME.'_'.tao_helpers_Uri::encode($user->uriResource);
		if(Session::hasAttribute($sessionKey)){
			$executionEnvironment = Session::getAttribute($sessionKey);
			if(isset($executionEnvironment['token']) && $executionEnvironment[CLASS_PROCESS_EXECUTIONS]['uri'] == $processExecution->uriResource ){
				return $executionEnvironment;
			}
		}
			
		$executionEnvironment = array(
			'token' => self::createToken(),
			CLASS_PROCESS_EXECUTIONS => array(
				'uri'		=> $processExecution->uriResource,
				RDFS_LABEL	=> $processExecution->getLabel()
			),
			TAO_SUBJECT_CLASS => array(
				'uri'					=> $user->uriResource,
				RDFS_LABEL				=> $user->getLabel(),
				PROPERTY_USER_LOGIN		=> (string)$user->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_LOGIN)),
				PROPERTY_USER_FIRTNAME	=> (string)$user->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_FIRTNAME)),
				PROPERTY_USER_LASTNAME	=> (string)$user->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_LASTNAME))
			)
		);
		Session::setAttribute($sessionKey, $executionEnvironment);
		return  $executionEnvironment;
	}
	
	/**
	 * Get the data of the current execution
	 * @return array
	 */
	protected function getExecutionEnvironment(){
		$currentUser = $this->userService->getCurrentUser();
		if(!is_null($currentUser)){
			$sessionKey =  self::ENV_VAR_NAME . '_' . tao_helpers_Uri::encode($currentUser->uriResource);
			
			if(Session::hasAttribute($sessionKey)){
				$executionEnvironment = Session::getAttribute($sessionKey);
				
				if(isset($executionEnvironment['token'])){
					return $executionEnvironment;
				}
			}
		}
		return array();
	}
	
	/**
	 * Get the folder where is the current compiled item
	 * @param array $executionEnvironment
	 * @return string
	 */
	protected function getCompiledFolder($executionEnvironment){
		
		$folder = '';
		
		if( isset($executionEnvironment[TAO_ITEM_CLASS]['uri']) && 
		 	isset($executionEnvironment[TAO_TEST_CLASS]['uri']) &&
		 	isset($executionEnvironment[TAO_DELIVERY_CLASS]['uri'])
		 	){
					
			$item 		= new core_kernel_classes_Resource($executionEnvironment[TAO_ITEM_CLASS]['uri']);
			$test 		= new core_kernel_classes_Resource($executionEnvironment[TAO_TEST_CLASS]['uri']);
			$delivery 	= new core_kernel_classes_Resource($executionEnvironment[TAO_DELIVERY_CLASS]['uri']);
			
			$deliveryFolder = substr($delivery->uriResource, strpos($delivery->uriResource, '#') + 1);
			$testFolder 	= substr($test->uriResource, strpos($test->uriResource, '#') + 1);
			$itemFolder 	= substr($item->uriResource, strpos($item->uriResource, '#') + 1);
			
			$compiledFolder = BASE_PATH. "/compiled/{$deliveryFolder}/{$testFolder}/{$itemFolder}/";
			
			if(is_dir($compiledFolder)){
				return $compiledFolder;
			}
		}
		
		return $folder;
	}
	
	/**
	 * Enbales you to authenticate a communication based on the token
	 * @param string $token
	 * @return boolean
	 */
	protected function authenticate($token){
		
		if(!empty($token)){
			
			$currentUser = $this->userService->getCurrentUser();
			if(!is_null($currentUser)){
				$sessionKey =  self::ENV_VAR_NAME . '_' . tao_helpers_Uri::encode($currentUser->uriResource);
				
				if(Session::hasAttribute($sessionKey)){
					$executionData = Session::getAttribute($sessionKey);
					
					if(isset($executionData['token'])){
						if($executionData['token'] == $token){
							return true;
						}
					}
				}
			}
		}
		return false;
	}
	
}
?>