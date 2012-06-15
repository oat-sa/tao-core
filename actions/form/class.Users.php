<?php

error_reporting(E_ALL);

/**
 * This container initialize the user edition form.
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Create a form from a  resource of your ontology. 
 * Each property will be a field, regarding it's widget.
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/actions/form/class.Instance.php');

/* user defined includes */
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF8-includes begin
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF8-includes end

/* user defined constants */
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF8-constants begin
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DF8-constants end

/**
 * This container initialize the user edition form.
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Users
    extends tao_actions_form_Instance
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute user
     *
     * @access protected
     * @var Resource
     */
    protected $user = null;

    /**
     * Short description of attribute formName
     *
     * @access protected
     * @var string
     */
    protected $formName = '';

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Class clazz
     * @param  Resource user
     * @param  boolean forceAdd
     * @param  boolean requireOldPassword
     * @return mixed
     */
    public function __construct( core_kernel_classes_Class $clazz,  core_kernel_classes_Resource $user = null, $forceAdd = false, $requireOldPassword = true)
    {
        // section 127-0-1-1-7dfb074:128afd58ed5:-8000:0000000000001F43 begin
        
    	if(is_null($clazz)){
    		throw new Exception('Set the user class in the parameters');	
    	}
    	
    	$this->formName = 'user_form';
    	
    	$options = array();
    	$service = tao_models_classes_UserService::singleton();
    	if(!is_null($user)){
    		$this->user = $user;
			$options['mode'] = 'edit';
    	}
    	else{
    		if(isset($_POST[$this->formName.'_sent']) && isset($_POST['uri'])){
    			$this->user = new core_kernel_classes_Resource(tao_helpers_Uri::decode($_POST['uri']));
    		}
    		else{
    			$this->user = $service->createInstance($clazz, $service->createUniqueLabel($clazz));
    		}
    		$options['mode'] = 'add';
    	}
    	if($forceAdd){
    		$options['mode'] = 'add';
    	}
    	common_Logger::d('user is '.$this->user);
    	
    	$options['topClazz'] = CLASS_GENERIS_USER;
    	$options['requireOldPassword'] = $requireOldPassword;
    	
    	parent::__construct($clazz, $this->user, $options);
    	
        // section 127-0-1-1-7dfb074:128afd58ed5:-8000:0000000000001F43 end
    }

    /**
     * Short description of method getUser
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return core_kernel_classes_Resource
     */
    public function getUser()
    {
        $returnValue = null;

        // section 127-0-1-1--65048268:128b57ca3f4:-8000:0000000000001F6B begin
        
        $returnValue = $this->user;
        
        // section 127-0-1-1--65048268:128b57ca3f4:-8000:0000000000001F6B end

        return $returnValue;
    }

    /**
     * Short description of method initForm
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    protected function initForm()
    {
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DFA begin
		
    	parent::initForm();
    	
    	$this->form->setName($this->formName);
    	
		$actions = tao_helpers_form_FormFactory::getCommonActions('top');
		$this->form->setActions($actions, 'top');
		$this->form->setActions($actions, 'bottom');
		
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DFA end
    }

    /**
     * Short description of method initElements
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    protected function initElements()
    {
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DFC begin
		
		if(!isset($this->options['mode'])){
			throw new Exception("Please set a mode into container options ");
		}
		
		parent::initElements();
		
		//login field
		$loginElement = $this->form->getElement(tao_helpers_Uri::encode(PROPERTY_USER_LOGIN));
		$loginElement->setDescription(__('Login *'));
		if($this->options['mode'] == 'add'){
			$loginElement->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('NotEmpty'),
				tao_helpers_form_FormFactory::getValidator('Callback', array(
					'object' => tao_models_classes_UserService::singleton(), 
					'method' => 'loginAvailable', 
					'message' => __('login already exist') 
				))
			));
		}
		else{
			$loginElement->setAttributes(array('readonly' => 'true'));
		}
		
		
		//set default lang to the languages fields
		$dataLangElt = $this->form->getElement(tao_helpers_Uri::encode(PROPERTY_USER_DEFLG));
		$options = $dataLangElt->getOptions();
		foreach($options as $key => $value){
			$options[$key] = __($value);
		}
		$dataLangElt->setOptions($options);
		
		
		$uiLangElt	= $this->form->getElement(tao_helpers_Uri::encode(PROPERTY_USER_UILG));
        $uiLangElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
        $uiLangElt->setDescription(__("Interface Language *"));
   		$options = $uiLangElt->getOptions();
		foreach($options as $key => $value){
			$options[$key] = __($value);
		}
		$uiLangElt->setOptions($options);
		
		//password field
		
		$this->form->removeElement(tao_helpers_Uri::encode(PROPERTY_USER_PASSWORD));
		
		if($this->options['mode'] == 'add'){
			$pass1Element = tao_helpers_form_FormFactory::getElement('password1', 'Hiddenbox');
			$pass1Element->setDescription(__('Password *'));
			$pass1Element->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('NotEmpty'),
				tao_helpers_form_FormFactory::getValidator('Length', array('min' => 3))
			));
			$this->form->addElement($pass1Element);
			
			$pass2Element = tao_helpers_form_FormFactory::getElement('password2', 'Hiddenbox');
			$pass2Element->setDescription(__('Repeat password *'));
			$pass2Element->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('NotEmpty'),
				tao_helpers_form_FormFactory::getValidator('Password', array('password2_ref' => $pass1Element)),
			));
			$this->form->addElement($pass2Element);
		}
		else {
			
			if (in_array(TAO_RELEASE_STATUS, array('demoA', 'demoB', 'demoS'))) {
				$warning  = tao_helpers_form_FormFactory::getElement('warningpass', 'Label');
				$warning->setValue(__('Unable to change passwords in demo mode'));
				$this->form->addElement($warning);
				$this->form->createGroup("pass_group", __("Change the password"), array('warningpass'));
			} else {
			
				if ($this->options['requireOldPassword']) {
					$pass1Element = tao_helpers_form_FormFactory::getElement('password1', 'Hiddenbox');
					$pass1Element->setDescription(__('Old Password'));
					$pass1Element->addValidator(
						tao_helpers_form_FormFactory::getValidator('Callback', array(
							'message'	=> __('Passwords are not matching'), 
							'object'	=> core_kernel_users_Service::singleton(),
							'method'	=> 'isPasswordValid',
							'param'		=> $this->getUser()
					)));
					$this->form->addElement($pass1Element);
				}
				
				$pass2Element = tao_helpers_form_FormFactory::getElement('password2', 'Hiddenbox');
				$pass2Element->setDescription(__('New password'));
				$pass2Element->addValidators(array(
					tao_helpers_form_FormFactory::getValidator('Length', array('min' => 3))
				));
				$this->form->addElement($pass2Element);
				
				$pass3Element = tao_helpers_form_FormFactory::getElement('password3', 'Hiddenbox');
				$pass3Element->setDescription(__('Repeat new password'));
				$pass3Element->addValidators(array(
					tao_helpers_form_FormFactory::getValidator('Password', array('password2_ref' => $pass2Element)),
				));
				$this->form->addElement($pass3Element);
				
				$this->form->createGroup("pass_group", __("Change the password"), array('password1', 'password2', 'password3'));
			}
		}
		/**/
		
		
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DFC end
    }

} /* end of class tao_actions_form_Users */

?>