<?php
/**
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
 *               2013-2018 (update and modification) Open Assessment Technologies SA;
 */

use oat\oatbox\filesystem\File;
use oat\tao\model\upload\UploadService;
use oat\tao\model\websource\WebsourceManager;
use oat\tao\model\websource\ActionWebSource;
use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\generis\model\OntologyAwareTrait;

/**
 *
 * Controller use for the file upload components
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao

 */
class tao_actions_File extends tao_actions_CommonModule
{
    use OntologyAwareTrait;

	/**
	 * Upload a file using http and copy it from the tmp dir to the target folder
	 * @return void
	 */
	public function upload()
	{
		$response = array('uploaded' => false);

		foreach ((array)$_FILES as $file) {
			$targetFolder = isset($_POST['folder']) ? $_POST['folder'] : '/';
			$response = array_merge($response, $this->uploadFile($file, $targetFolder . '/'));
		}
        $this->returnJson($response);
	}

    /**
     * Get, check and move the file uploaded (described in the posetedFile parameter)
     *
     * @param array $postedFile
     * @param string $folder
     * @return array $data
     * @throws \oat\oatbox\service\ServiceNotFoundException
     * @throws \common_Exception
     */
	protected function uploadFile($postedFile, $folder)
	{
        $returnValue = [];

        if (isset($postedFile['tmp_name'], $postedFile['name']) && $postedFile['tmp_name']) {
            $returnValue = $this->getServiceLocator()->get(UploadService::SERVICE_ID)->uploadFile($postedFile, $folder);
        }
        return $returnValue;
	}

	/**
	 * Download a resource file content
	 * @param {String} uri Uri of the resource file
	 */
	public function downloadFile()
	{
		if($this->hasRequestParameter('id')){
			$fileService = $this->getServiceLocator()->get(FileReferenceSerializer::SERVICE_ID);
			$file = $fileService->unserialize($this->getRequestParameter('id'));
			header("Content-Disposition: attachment; filename=\"{$file->getBasename()}\"");
			tao_helpers_Http::returnStream($file->readPsrStream(), $file->getMimeType());
		}

	}

	public function getPropertyFileInfo()
    {
		$data = array('name' => __('(empty)'));

		if ($this->hasRequestParameter('uri') && $this->hasRequestParameter('propertyUri')) {
			$uri = tao_helpers_Uri::decode($this->getRequestParameter('uri'));
			$propertyUri = tao_helpers_Uri::decode($this->getRequestParameter('propertyUri'));
			$instance = $this->getResource($uri);
			$fileResource = $instance->getOnePropertyValue($this->getProperty($propertyUri));

			if (!is_null($fileResource) && $fileResource instanceof core_kernel_classes_Resource) {
                /** @var FileReferenceSerializer $fileService */
                $fileService = $this->getServiceLocator()->get(FileReferenceSerializer::SERVICE_ID);
                $file = $fileService->unserialize($fileResource);

                if ($file instanceof File) {
                    $data['name'] = $file->getBasename();
                }
			}
		}

		return $this->returnJson($data);
	}

    /**
     * @throws ResolverException
     * @throws \oat\tao\model\websource\WebsourceNotFound
     * @throws tao_models_classes_FileNotFoundException
     */
    public function accessFile()
    {
        list($extension, $module, $action, $code, $filePath) = explode('/', tao_helpers_Request::getRelativeUrl(), 5);;
        list($key, $subPath) = explode(' ', base64_decode($code), 2);

        $source = WebsourceManager::singleton()->getWebsource($key);
        if ($source instanceof ActionWebSource) {
            $path = $subPath.(empty($filePath) ? '' : DIRECTORY_SEPARATOR . $filePath);
            tao_helpers_Http::returnStream($source->getFileStream($path), $source->getMimetype($path));
        }
    }
}
