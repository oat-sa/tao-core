<?php
/**
 * 
 * Controller use for the file upload components
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 */
class File extends CommonModule{
	
	/**
	 * @var string $rootFolder root folder of the copyed files
	 */
	protected $rootFolder = '';

	/**
	 * constructor. Initialize the context
	 */
	public function __construct(){
		$this->rootFolder = TAOVIEW_PATH .'/tmp';
	}
	
	/**
	 * Upload a file using http and copy it from the tmp dir to the target folder
	 * @return void
	 */
	public function upload(){
		$response = array('uploaded' => false);
		if (!empty($_FILES)) {
			
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $this->rootFolder . $_REQUEST['folder'] . '/';
			if(tao_helpers_File::securityCheck($targetPath)){
				if(!file_exists($targetPath)){
					mkdir($targetPath);
				}
				$targetFile =  tao_helpers_File::concat(array($targetPath, $_FILES['Filedata']['name']));
				if(move_uploaded_file($tempFile, $targetFile)){
					$response['uploaded'] = true;
					$data = $_FILES['Filedata'];
					$data['type'] =  tao_helpers_File::getMimeType($targetFile);
					$data['uploaded_file'] = $targetFile;
					$response['data'] = serialize($data);
				}
			}
		}
		echo json_encode($response);
		return;
	}
}
?>