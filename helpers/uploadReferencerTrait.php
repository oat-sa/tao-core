<?php

namespace oat\tao\helpers;

use oat\generis\model\fileReference\UrlFileSerializer;
use oat\oatbox\filesystem\File;
use oat\oatbox\service\ServiceManager;

trait uploadReferencerTrait
{
    private $uriSerializer;

    protected function getSerializer()
    {
        if (!$this->uriSerializer) {
            $this->uriSerializer = new UrlFileSerializer();
            $this->uriSerializer->setServiceLocator(ServiceManager::getServiceManager());
        }
        return $this->uriSerializer;
    }

    protected function universalizeUpload($file)
    {
        if (filter_var($file, FILTER_VALIDATE_URL)) {
            return $this->getSerializer()->unserializeFile($file);
        }
        if (is_file($file)) {
            return $file;
        }

        throw new \common_Exception('Unsupported file reference');
    }

    /**
     * @param $serial
     * @return string
     */
    protected function getLocalCopy($serial)
    {
        $file = $this->universalizeUpload($serial);
        if ($file instanceof File) {
            $tmpName = \tao_helpers_File::concat([\tao_helpers_File::createTempDir(), $file->getPrefix()]);
            file_put_contents($tmpName, $file->read());
            return $tmpName;
        }
        return $serial;
    }
}