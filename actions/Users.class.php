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
		
		if(!$sidx) $sidx =1; 
		
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
		
		$myFormContainer = new tao_actions_form_Users(array(), array('mode' => 'add'));
		$myForm = $myFormContainer->getForm();
		
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
		
		$myFormContainer = new tao_actions_form_Users(
			$this->userService->getOneUser($this->getRequestParameter('login')),
			array('mode' => 'edit')
		);
		$myForm = $myFormContainer->getForm();
		
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
	
}
?>