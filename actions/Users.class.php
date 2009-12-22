<?php
/**
 * This controller provide the actions to manage the application users (list/add/edit/delete)
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class Users extends CommonModule {

	protected $userService = null;
	
	/**
	 * Constructor performs initializations actions
	 * @return void
	 */
	public function __construct(){
		$this->userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
		$this->defaultData();
	}

	/**
	 * Show the list of users
	 * @return void
	 */
	public function index(){
		$this->setData('data', 'list the users');
		$this->setView('user_list.tpl');
	}
	
	/**
	 * provide the user list data via json
	 * @return void
	 */
	public function data(){
		$page = $this->getRequestParameter('page'); 
		$limit = $this->getRequestParameter('rows'); 
		$sidx = $this->getRequestParameter('sidx');  
		$sord = $this->getRequestParameter('sord'); 
		$start = $limit * $page - $limit; 
		
		if(!$sidx) $sidx =1; // connect to the database 
		
		$users = $this->userService->getAllUsers(array(
			'order' 	=> $sidx,
			'orderDir'	=> $sord,
			'start'		=> $start,
			'end'		=> $limit
		));
		
		$count = count($users); 
		if( $count >0 ) { 
			$total_pages = ceil($count/$limit); 
		} 
		else { 
			$total_pages = 0; 
		} 
		if ($page > $total_pages){
			$page = $total_pages; 
		}
		
		$response = new stdClass();
		$response->page = $page; 
		$response->total = $total_pages; 
		$response->records = $count; 
		foreach($users as $i => $user) { 
			$response->rows[$i]['id']= $user['login']; 
			$response->rows[$i]['cell']= array(
				"<img src='".BASE_WWW."img/user_go.png' alt='".__('Active user')."' />",
				$user['login'],
				$user['FirstName'],
				$user['LastName'],
				$user['E_Mail'],
				$user['Company'],
				$user['Deflg'],
				"<a href='#' onclick='editUser(\"".$user['login']."\");'><img src='".BASE_WWW."img/pencil.png' alt='".__('Edit user')."' title='".__('edit')."' /></a>&nbsp;|&nbsp;" .
				"<a href='#' onclick='if(confirm(\"".__('Please confirm user deletion')."\")){ window.location=\""._url('delete', 'Users', array('login' => $user['login']))."\"; }' ><img src='".BASE_WWW."img/delete.png' alt='".__('Delete user')."' title='".__('delete')."' /></a>"
			);
		} 
		echo json_encode($response); 
	}
	
	/**
	 * Remove a user
	 * The request must contains the user's login to remove
	 * @return vois
	 */
	public function delete(){
		$message = __('An error occured during user deletion');
		if($this->hasRequestParameter('login')){
			if($this->userService->removeUser($this->getRequestParameter('login'))){
				$message = __('User deleted successfully');
			}
		}
		$this->redirect(_url('index', 'Main', array('extension' => 'users', 'message' => $message)));
	}
	
	/**
	 * form to add a user
	 * @return void
	 */
	public function add(){
		$myForm = $this->initUserForm('add');
		
		if($myForm->isSubmited()){
			
			if($myForm->isValid()){
				$values = $myForm->getValues();
				$values['password'] = md5($values['password1']);
				unset($values['password1']);
				unset($values['password2']);
				if($this->userService->saveUser($values)){
					$this->setData('message', __('User added'));
					$this->setData('exit', true);
				}
			}
		}
		$this->setData('formTitle', __('Add a user'));
		$this->setData('myForm', $myForm->render());
		$this->setView('user_form.tpl');
	}
	
	/**
	 * Form to edit a user
	 * User login must be set in parameter
	 * @return  void
	 */
	public function edit(){
		
		if(!$this->hasRequestParameter('login')){
			throw new Exception('Please set the user login in request parameter');
		}
		
		$myForm = $this->initUserForm('edit', $this->userService->getOneUser($this->getRequestParameter('login')));
		
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				$values = $myForm->getValues();
				
				if(!empty($values['password1']) && !empty($values['password2'])){
					$values['password'] = md5($values['password2']);
				}
				else{
					$values['password'] = $values['password0'];
				}
				unset($values['password0']);
				unset($values['password1']);
				unset($values['password2']);
				unset($values['password3']);
				
				if($this->userService->saveUser($values)){
					$this->setData('message', __('User saved'));
					$this->setData('exit', true);
				}
			}
		}
		
		$this->setData('formTitle', __('Edit a user'));
		$this->setData('myForm', $myForm->render());
		$this->setView('user_form.tpl');
	}
	
	/**
	 * restore the default user
	 * @return void
	 */
	public function restore(){
		$defaultUser = array(
				'login'		=> 'tao',
				'password'	=> md5('tao'),
				'LastName'	=> '',
				'FirstName' => '',
				'E_Mail'	=> '',
				'Company'	=> '',
				'Deflg'		=> 'EN'
			);
		
		$message = __('Unable to restore default user');
		if($this->userService->saveUser($defaultUser)){
			$message = __('User restored successfully');
		}
		
		$this->redirect(_url('index', 'Main', array('extension' => 'users', 'message' => $message)));
	}
	
	/**
	 * Initialize the Form component for user 
	 * @param string $mode add or edit mode
	 * @param array $data the default form data
	 * @return tao_helpers_form_Form the user form
	 */
	private function initUserForm($mode = 'add', $data = array()){
		$myForm = tao_helpers_form_FormFactory::getForm('users', array('noRevert' => true));
		
		//login field
		$loginElement = tao_helpers_form_FormFactory::getElement('login', 'Textbox');
		$loginElement->setDescription(__('Login *'));
		if($mode == 'add'){
			$loginElement->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('NotEmpty'),
				tao_helpers_form_FormFactory::getValidator('Callback', array(
					'class' => 'tao_models_classes_UserService', 
					'method' => 'loginAvailable', 
					'message' => __('login already exist') 
				))
			));
		}
		else{
			$loginElement->setAttributes(array('readonly' => 'true'));
			if(isset($data['login'])){
				$loginElement->setValue($data['login']);
			}
		}
		$myForm->addElement($loginElement);
		
		//password field
		if($mode == 'add'){
			$pass1Element = tao_helpers_form_FormFactory::getElement('password1', 'Hiddenbox');
			$pass1Element->setDescription(__('Password *'));
			$pass1Element->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('NotEmpty'),
				tao_helpers_form_FormFactory::getValidator('Length', array('min' => 4))
			));
			$myForm->addElement($pass1Element);
			
			$pass2Element = tao_helpers_form_FormFactory::getElement('password2', 'Hiddenbox');
			$pass2Element->setDescription(__('Repeat password *'));
			$pass2Element->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('NotEmpty'),
				tao_helpers_form_FormFactory::getValidator('Password', array('password2_ref' => $pass1Element)),
			));
			$myForm->addElement($pass2Element);
		}
		else{
			
			$validatePasswords = true;
			if(isset($_POST['users_sent']) && isset($_POST['password1'])){
				if(empty($_POST['password1'])) {
					$validatePasswords = false;
				}
			}
			
			$pass0Element = tao_helpers_form_FormFactory::getElement('password0', 'Hidden');
			if(isset($data['password'])){
				$pass0Element->setValue($data['password']);
			}
			$myForm->addElement($pass0Element);
			
			$pass1Element = tao_helpers_form_FormFactory::getElement('password1', 'Hiddenbox');
			$pass1Element->setDescription(__('Old Password'));
			$pass1Element->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('Md5Password', array('password2_ref' => $pass0Element)),
			));
			if(!$validatePasswords){
				$pass1Element->setForcedValid();
			}
			$myForm->addElement($pass1Element);
			
			$pass2Element = tao_helpers_form_FormFactory::getElement('password2', 'Hiddenbox');
			$pass2Element->setDescription(__('New password'));
			$pass2Element->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('Length', array('min' => 4))
			));
			if(!$validatePasswords){
				$pass2Element->setForcedValid();
			}
			$myForm->addElement($pass2Element);
			
			$pass3Element = tao_helpers_form_FormFactory::getElement('password3', 'Hiddenbox');
			$pass3Element->setDescription(__('Repeat new password'));
			$pass3Element->addValidators(array(
				tao_helpers_form_FormFactory::getValidator('Password', array('password2_ref' => $pass2Element)),
			));
			if(!$validatePasswords){
				$pass3Element->setForcedValid();
			}
			$myForm->addElement($pass3Element);
			
			$myForm->createGroup("pass_group", "Change your password", array('password0', 'password1', 'password2', 'password3'));
		}
		
		//firstname field
		$fNameElement = tao_helpers_form_FormFactory::getElement('FirstName', 'Textbox');
		$fNameElement->setDescription(__('FirstName'));
		if(isset($data['FirstName'])){
			$fNameElement->setValue($data['FirstName']);
		}
		$myForm->addElement($fNameElement);
		
		//lastname field
		$lNameElement = tao_helpers_form_FormFactory::getElement('LastName', 'Textbox');
		$lNameElement->setDescription(__('LastName'));
		if(isset($data['LastName'])){
			$lNameElement->setValue($data['LastName']);
		}
		$myForm->addElement($lNameElement);
		
		//email field 
		$emailElement = tao_helpers_form_FormFactory::getElement('E_Mail', 'Textbox');
		$emailElement->setDescription(__('Email'));
		if(isset($data['E_Mail'])){
			$emailElement->setValue($data['E_Mail']);
		}
		$myForm->addElement($emailElement);
		
		//company field
		$companyElement = tao_helpers_form_FormFactory::getElement('Company', 'Textbox');
		$companyElement->setDescription(__('Company'));
		if(isset($data['Company'])){
			$companyElement->setValue($data['Company']);
		}
		$myForm->addElement($companyElement);
		
		//language field
		$lgElement = tao_helpers_form_FormFactory::getElement('Deflg', 'Textbox');
		$lgElement->setDescription(__('Language *'));
		$lgElement->setAttributes(array('size' => 6));
		$lgElement->addValidators(array(
			tao_helpers_form_FormFactory::getValidator('NotEmpty'),
			tao_helpers_form_FormFactory::getValidator('Regex', array('format' => "/^[A-Z]{2,3}$/"))
		));
		if($mode == 'edit' && isset($data['Deflg'])){
			$lgElement->setValue($data['Deflg']);
		}
		else{
			$lgElement->setValue($GLOBALS['lang']);
		}
		$myForm->addElement($lgElement);
		
		
		//Evaluate here
		$myForm->evaluate();
		
		
		return $myForm;
	}
}
?>