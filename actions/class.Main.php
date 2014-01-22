<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php
/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class tao_actions_Main extends tao_actions_CommonModule {

	/**
	 * Constructor performs initializations actions
	 * @return void
	 */
	public function __construct()
	{
		//initialize service
		$this->service = tao_models_classes_TaoService::singleton();
		$this->defaultData();

	}

	/**
	 * First page, when arriving on a system
	 * to choose front or back office
	 */
	public function entry() {
	    $entries = array();
	    foreach (common_ext_ExtensionsManager::singleton()->getInstalledExtensions() as $extension) {
	        foreach ($extension->getEntryPoints() as $entry) {
	            $hasAccess = tao_models_classes_accessControl_AclProxy::hasAccess($entry['ext'], $entry['mod'], $entry['act']);
	            if ($hasAccess) {
	                $entries[] = $entry;
	            }
	        }
	    }
	    if (empty($entries)) {
	        // no access -> error
	        if (common_session_SessionManager::isAnonymous()) {
	           return $this->redirect(_url('login')); 
	        } else {
	            common_session_SessionManager::endSession();
                return $this->returnError(__('You currently have no access to the platform'));
	        }
	    } elseif (count($entries) == 1 && !common_session_SessionManager::isAnonymous()) {
	        // single entrypoint -> redirect
	        $entry = current($entries);
	        return $this->redirect(_url($entry['act'], $entry['mod'], $entry['ext']));
	    } else {
	        // multiple entries -> choice
	        if (!common_session_SessionManager::isAnonymous()) {
	            $this->setData('user', common_session_SessionManager::getSession()->getUserLabel());
	        }
    	    $this->setData('entries', $entries);
    		$this->setView('entry.tpl');
	    }
	}
	
	/**
	 * Authentication form,
	 * default page, main entry point to the user
	 * @return void
	 */
	public function login()
	{
		//add the login stylesheet
		tao_helpers_Scriptloader::addCssFile(TAOBASE_WWW . 'css/login.css');

		$params = array();
		if ($this->hasRequestParameter('redirect')) {
			$redirectUrl = $_REQUEST['redirect'];
				
			if (substr($redirectUrl, 0,1) == '/' || substr($redirectUrl, 0, strlen(ROOT_URL)) == ROOT_URL) {
				$params['redirect'] = $redirectUrl;
			}
		}
		$myLoginFormContainer = new tao_actions_form_Login($params);
		$myForm = $myLoginFormContainer->getForm();

		if($myForm->isSubmited()){
			if($myForm->isValid()){
				$adapter = new core_kernel_users_AuthAdapter($myForm->getValue('login'), $myForm->getValue('password'));
				if(common_user_auth_Service::singleton()->login($adapter)){
					if ($this->hasRequestParameter('redirect')) {
						$this->redirect($_REQUEST['redirect']);
					} else {
						$this->redirect(_url('entry', 'Main'));
					}
				}
				else{
					$this->setData('errorMessage', __('Invalid login or password. Please try again.'));
				}
			}
		}

        $this->setData('form', $myForm->render());
        $this->setData('title', __("TAO Login"));
        if ($this->hasRequestParameter('msg')) {
            $this->setData('msg', htmlentities($this->getRequestParameter('msg')));
        }
		$this->setView('main/login.tpl');
	}

	/**
	 * Logout, destroy the session and back to the login page
	 * @return
	 */
	public function logout()
	{
		session_destroy();
		$this->redirect(_url('entry', 'Main', 'tao'));
	}

	/**
	 * The main action, load the layout
	 * @return void
	 */
	public function index()
	{
		$extensions = array();
		foreach ($this->service->getAllStructures() as $i => $structure) {
			if ($structure['data']['visible'] == 'true') {
				$data = $structure['data'];
				$extensions[$i] = array(
					'id'			=> (string) $structure['id'],
					'name' 			=> (string) $data['name'],
					'extension'		=> $structure['extension'],
					'description'	=> (string) $data->description
				);

				//Test if access
				$access = false;
				foreach ($data->sections->section as $section) {
					list($ext, $mod, $act) = explode('/', trim((string) $section['url'], '/'));
					if (tao_models_classes_accessControl_AclProxy::hasAccess($ext, $mod, $act)) {
						$access = true;
						break;
					}
				}
				$extensions[$i]['enabled'] = $access;
			}
		}
		$this->setData('extensions', $extensions);

                $shownExtension = $this->getRequestParameter('ext');
                $shownStructure = $this->getRequestParameter('structure');
		if($this->hasRequestParameter('structure')) {
			// structured mode
			// @todo stop using session to manage uri/classUri
			$this->removeSessionAttribute('uri');
			$this->removeSessionAttribute('classUri');
			$this->removeSessionAttribute('showNodeUri');
			$structure = $this->service->getStructure($shownExtension, $shownStructure);

			$sections = array();
			if (isset($structure["sections"])) {
				foreach ($structure["sections"] as $section) {
					$url = explode('/', substr((string)$section['url'], 1));
					$ext = (isset($url[0])) ? $url[0] : null;
					$module = (isset($url[1])) ? $url[1] : null;
					$action = (isset($url[2])) ? $url[2] : null;
	
					if (tao_models_classes_accessControl_AclProxy::hasAccess($ext, $module, $action)) {
						$sections[] = array('id' => (string)$section['id'], 'url' => (string)$section['url'], 'name' => (string)$section['name']);
					}
				}
			}

			if (count($sections) > 0) {
				$this->setData('sections', $sections);
			} else {
				common_Logger::w('no sections');
			}
		} else {
			// home screen
			$this->setData('sections', false);
			tao_helpers_Scriptloader::addCssFile(TAOBASE_WWW . 'css/home.css');
		}

		$this->setData('user_lang', core_kernel_classes_Session::singleton()->getDataLanguage());
		$this->setData('userLabel', core_kernel_classes_Session::singleton()->getUserLabel());
                
                //creates the URL of the action used to configure the client side
                $clientConfigParameters = array(
                    'shownExtension'    => $shownExtension,
                    'shownStructure'    => $shownStructure
                );
                $this->setData('client_config_url', $this->getClientConfigUrl($clientConfigParameters));

		$this->setView('layout.tpl', 'tao');
	}

    /**
     * Check if the system is ready
     */
    public function isReady(){
		if(tao_helpers_Request::isAjax()){
            // the default ajax response is successfull style rastafarai
            $ajaxResponse = new common_AjaxResponse();
        }
        else{
            throw new common_exception_IsAjaxAction(__CLASS__.'::'.__METHOD__.'()');
        }
    }

	/**
	 * Load the actions for the current section and the current data context
	 * @return void
	 */
	public function getSectionActions()
	{

		$uri = $this->hasRequestParameter('uri');
		$classUri = $this->hasRequestParameter('classUri');
		$extname = $this->hasRequestParameter('ext');
		$struct = $this->getRequestParameter('structure');

		$rootClasses = array(TAO_GROUP_CLASS, TAO_ITEM_CLASS, TAO_RESULT_CLASS, TAO_SUBJECT_CLASS, TAO_TEST_CLASS);

		$this->setData('actions', false);
		$this->setData('shownExtension', $this->getRequestParameter('ext'));

		$structure = $this->service->getSection($extname, $struct, $this->getRequestParameter('section'));
		if(isset($structure["actions"])){
			$actionNodes =  $structure["actions"];
			$actions = array();
			foreach($actionNodes as $actionNode){
				$nocheck = true;
				if (isset($actionNode['url']) && strlen((string)$actionNode['url']) > 0) {
					$url = explode('/', substr((string)$actionNode['url'], 1));
					if (count($url) == 3) {
						$ext = (isset($url[0])) ? $url[0] : null;
						$module = (isset($url[1])) ? $url[1] : null;
						$action = (isset($url[2])) ? $url[2] : null;
						$nocheck = false;
					}
				}

				if ($nocheck || tao_models_classes_accessControl_AclProxy::hasAccess($ext, $module, $action)) {
					$display = __((string) $actionNode['name']);
					if(strlen($display) > 15){
						$display = str_replace(' ', "<br>", $display);
					}
					$action = array(
						'js'		=> (isset($actionNode['js'])) ? (string) $actionNode['js'] : false,
						'url' 		=> ROOT_URL . substr((string)$actionNode['url'], 1),
						'display'	=> $display,
						'rowName'	=> (string) $actionNode['name'],
						'name'		=> _clean((string) $actionNode['name']),
						'uri'		=> ($uri) ? $this->getRequestParameter('uri') : false,
						'classUri'	=> ($classUri) ? $this->getRequestParameter('classUri') : false,
						'reload'	=> (isset($actionNode['reload'])) ? true : false,
					    'ext'       => $ext
					);

					$action['disabled'] = true;
					switch ((string) $actionNode['context']) {
						case 'resource':
							if ($classUri || $uri) {
                                $action['disabled'] = false;
                            }
							break;
						case 'class':
							if ($classUri && !$uri) {
                                $action['disabled'] = false;
                            }
							break;
						case 'instance':
							if ($classUri && $uri) {
                                $action['disabled'] = false;
                            }
							break;
						case '*':
							$action['disabled'] = false;
							break;
						default:
							$action['disabled'] = true;
							break;
					}

					//@todo remove this when permissions engine is setup
					if ($action['rowName'] == 'delete' && $classUri && !$uri) {
						if (in_array($action['classUri'], tao_helpers_Uri::encodeArray($rootClasses, tao_helpers_Uri::ENCODE_ARRAY_VALUES))) {
							$action['disabled'] = true;
						}
					}

					array_push($actions, $action);
				}
			}

			$this->setData('actions', $actions);
		}

		$this->setView('main/actions.tpl', 'tao');
	}

	/**
	 * Load the section trees
	 * @return void
	 */
	public function getSectionTrees()
	{
            

            $currentNode = $this->getSessionAttribute("showNodeUri");

		$extname	= $this->getRequestParameter('ext');
		$struct		= $this->getRequestParameter('structure');
		$section	= $this->getRequestParameter('section');

		$structure = $this->service->getSection($extname, $struct, $section);
		if(isset($structure["trees"])){
                    $mapping = array(
                        'editClassUrl'      => 'editClassAction',
                        'editInstanceUrl'   => 'editInstanceAction',
                        'addInstanceUrl'    => 'createInstanceAction',
                        'moveInstanceUrl'   => 'moveInstanceAction',
                        'addSubClassUrl'    => 'subClassAction',
                        'deleteUrl'         => 'deleteAction',
                        'duplicateUrl'      => 'duplicateAction'
                    );
                    
                    $trees = array();
                    foreach($structure["trees"] as $tree){
                        $treeArray = array();
                        foreach($tree->attributes() as $attrName => $attrValue){
                            $key = (array_key_exists($attrName, $mapping)) ? $mapping[$attrName] : $attrName;  
                            if(preg_match("/^\//", (string) $attrValue)){
                                        $treeArray[$key] = ROOT_URL . substr((string)$attrValue, 1);
                                }
                                else{
                                        $treeArray[$key] = (string)$attrValue;
                                }
                        }
                        
                        if($this->hasSessionAttribute("showNodeUri")){
                            $treeArray['selectNode'] = $this->getSessionAttribute("showNodeUri");
                        }
                        if(isset($treeArray['className'])){
                            $treeArray['instanceClass'] = 'node-'.str_replace(' ', '-', strtolower($treeArray['className']));
                            $treeArray['instanceName'] = mb_strtolower(__($treeArray['className']), TAO_DEFAULT_ENCODING);
                        }
                        $treeId = tao_helpers_Display::textCleaner((string) $tree['name'], '_');
                        $trees[$treeId] = $treeArray;
                    }
                    $this->setData('trees', $trees);
		}

		$this->setView('main/trees.tpl', 'tao');
	}
}
?>
