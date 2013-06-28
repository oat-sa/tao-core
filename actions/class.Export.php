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
 * This controller provide the actions to export and manage exported data
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class tao_actions_Export extends tao_actions_CommonModule {

	/**
	 * get the path to save and retrieve the exported files regarding the current extension
	 * @return string the path
	 */
	protected function getExportPath($extension = null){

		$extension = is_null($extension) ? Context::getInstance()->getExtensionName() : $extension;
		$taoExt = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
		$exportPath = $taoExt->getConstant('EXPORT_PATH');

		if(!is_dir($exportPath)){
			common_Logger::i('Export path not found, creating '.$exportPath);
			if(!mkdir($exportPath)){
				throw new common_Exception("Unable to create {$exportPath}. Check your filesystem!");
			}
		}
		return $exportPath;
	}

	/**
	 * Does EVERYTHING
	 * @todo cleanup interface
	 */
	public function index(){
		$formData = array();
		if($this->hasRequestParameter('classUri')){
			if(trim($this->getRequestParameter('classUri')) != ''){
				$formData['class'] = new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
			}
		}
		if($this->hasRequestParameter('uri') && $this->hasRequestParameter('classUri')){
			if(trim($this->getRequestParameter('uri')) != ''){
				$formData['instance'] = new core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
			}
		}
		
		$handlers = $this->getAvailableExportHandlers();
		$exporter = $this->getCurrentExporter();
			
		$formFactory = new tao_actions_form_Export($handlers, $exporter->getForm($formData), $formData);
		$myForm = $formFactory->getForm();
		if (!is_null($exporter)) {
			$myForm->setValues(array('exportHandler' => get_class($exporter)));
		}
		$this->setData('myForm', $myForm->render());
		if ($myForm->isSubmited()) {
			if ($myForm->isValid()) {
				$file = $exporter->export($myForm->getValues(), $this->getExportPath());
				if (!is_null($file)) {
					$relPath = ltrim(substr($file, strlen($this->getExportPath())), DIRECTORY_SEPARATOR);
					$this->setData('download', _url('downloadExportedFiles', null, null, array('filePath' => $relPath)));
				}
			}
		}
		
		$this->setData('formTitle', __('Export'));
		$this->setView('form/export.tpl', 'tao');
	}
	
	public function doExport($exporter){
		common_Logger::i('running export using ExportHandler '.$exporter->getLabel());
		
		$expForm = $exporter->getForm(array());
		$file = $exporter->export($expForm->getValues(), $this->getExportPath());
		$this->setData('success', !is_null($file));
		if (!is_null($file)) {
			$relPath = ltrim(substr($file, strlen($this->getExportPath())), DIRECTORY_SEPARATOR);
			$this->setData('download', _url('downloadExportedFiles', null, null, array('filePath' => $relPath)));
		}
					
		$this->setView('form/exportResult.tpl', 'tao');
	}
	
	protected function getResourcesToExport(){
		$returnValue = array();
		if($this->hasRequestParameter('uri') && trim($this->getRequestParameter('uri')) != ''){
			$returnValue[] = new core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
		}elseif($this->hasRequestParameter('classUri') && trim($this->getRequestParameter('classUri')) != ''){
			$class = new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
			$returnValue = $class->getInstances();
		}else {
			common_Logger::w('No resources to export');
		}
		return $returnValue;
	}
	
	/**
	 * Returns the selected ExportHandler
	 * 
	 * @return tao_models_classes_Export_ExportHandler
	 * @throws common_Exception
	 */
	private function getCurrentExporter() {
		if ($this->hasRequestParameter('exportHandler')) {
			$exportHandler = $this->getRequestParameter('exportHandler');
			if (class_exists($exportHandler) && in_array('tao_models_classes_Export_ExportHandler', class_implements($exportHandler))) {
				$exporter = new $exportHandler();
				return $exporter;
			} else {
				throw new common_Exception('Unknown or incompatible ExporterHandler: \''.$exportHandler.'\'');
			}
		} else {
			return current($this->getAvailableExportHandlers());
		}
	}

	/**
	 * Override this function to add your own custom ExportHandlers
	 * 
	 * @return array an array of ExportHandlers
	 */
	protected function getAvailableExportHandlers() {
		return array(
			new tao_models_classes_Export_RdfExporter()
		);
	}
	
	/**
	 * Get the list of files exported for the current module
	 * The output is formated to be received by a JS Grid
	 * @return void
	 */
	public function getExportedFiles(){

		$exportPath = $this->getExportPath($this->hasRequestParameter('ext') ? $this->getRequestParameter('ext') : Context::getInstance()->getExtensionName());
		common_Logger::d('Listing exported files from '.$exportPath);
		
		$exportedFiles = array();
		foreach(scandir($exportPath) as $file){
			$path = $exportPath.'/'.$file;
			if(preg_match("/\.(rdf|zip)$/", $file) && !is_dir($path)){
				$exportedFiles[] = array(
					'path'		=> $file,
					'url'		=> str_replace(ROOT_PATH, ROOT_URL.'/', $path),
					'name'		=> substr($file, 0, strrpos($file, '_')),
					'date'		=> date('Y-m-d H:i:s', ((int)substr(preg_replace("/\.(rdf|zip)$/", '', $file), strrpos($file, '_') + 1)))
				);
			}
		}

		$page = $this->getRequestParameter('page');
		$limit = $this->getRequestParameter('rows');
		$sidx = $this->getRequestParameter('sidx');
		$sord = $this->getRequestParameter('sord');
		$start = $limit * $page - $limit;

		if(!$sidx) $sidx =1;

		//slice from start to limit
		$files = array_slice($exportedFiles, $start, $limit);

		$col = array();
		foreach($files as $key => $val){
			$col[$key] = $val[$sidx];
		}
		array_multisort($col, ($sord == 'asc') ? SORT_ASC: SORT_DESC, $files);

		$count = count($exportedFiles);
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
		foreach($files as $i => $file) {
			$response->rows[$i]['id']= $i;
			$response->rows[$i]['cell']= array(
				$file['name'],
				basename($file['path']),
				$file['date'],
				array($file['url'],
					_url('downloadExportedFiles', null, null, array('filePath' => $file['path'])),
					_url('deleteExportedFiles', null, null, array('filePath' => $file['path']))
				)
			);
		}
		echo json_encode($response);
	}

	/**
	 * remove the exported files in parameters
	 * @return void
	 */
	public function deleteExportedFiles(){
		$deleted = false;
		if($this->hasRequestParameter('filePath')){
			$path = $this->getExportPath().DIRECTORY_SEPARATOR.$_GET['filePath'];
			if(tao_helpers_File::securityCheck($path, true)){
				$deleted = tao_helpers_File::remove($path);
			}
		}
		echo json_encode(array('deleted' => $deleted));
	}

	/**
	 * download the exported files in parameters
	 * @return void
	 */
	public function downloadExportedFiles(){

		//get request directly since getRequest changes names
		$path = isset($_GET['filePath']) ? $_GET['filePath'] : '';
		$fullpath = $this->getExportPath().DIRECTORY_SEPARATOR.$path;		
		return $this->download($fullpath);
	}
	
	/**
	 * download the exported files in parameters
	 * @return void
	 */
	private function download($fullpath){

		if(tao_helpers_File::securityCheck($fullpath, true) && file_exists($fullpath)){
			$this->setContentHeader(tao_helpers_File::getMimeType($fullpath));
			header('Content-Disposition: attachment; fileName="'.basename($fullpath).'"');
			header("Content-Length: " . filesize($fullpath));
			flush();
			$fp = fopen($fullpath, "r");
			if ($fp !== false) {
				while (!feof($fp))
				{
				    echo fread($fp, 65536); 
				    flush();
				}  
				fclose($fp);
			} else {
 				common_Logger::e('Unable to open File to export' . $fullpath);				
			} 
		}
        else{
            common_Logger::e('Could not find File to export' . $fullpath);
        }

		return;
	}
}
?>
