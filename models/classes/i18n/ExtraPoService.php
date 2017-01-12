<?php

namespace oat\tao\model\i18n;
use oat\oatbox\service\ConfigurableService;

class ExtraPoService extends ConfigurableService
{
    const SERVICE_ID = 'tao/ExtraPoService';
    
    public function __construct(array $options = [])
    {
        if (isset($options['paths']) === false || is_array($options['paths']) === false) {
            $options['paths'] = [];
        }
        
        parent::__construct($options);
    }
    
    public function addPoPath($extensionId, $path)
    {
        $paths = $this->getOption('paths');
        
        try {
            $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById($extensionId);
            
            if (isset($paths[$ext->getId()]) === false) {
                $paths[$ext->getId()] = [];
            }
            
            if (in_array($path, $paths[$ext->getId()]) === false) {
                $paths[$ext->getId()][] = $path;
            }
            
            $this->setOption('paths', $paths);
            
            return true;
        
        } catch (\common_ext_ExtensionException $e) {
            return false;
        }
    }
    
    public function removePoPath($extensionId, $path)
    {
        $paths = $this->getOption('paths');
        
        try {
            $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById($extensionId);
            
            if (isset($paths[$ext->getId()]) === false) {
                return false;
            }
            
            $index = array_search($path, $paths[$ext->getId()]);
            
            if ($index === false) {
                return $index;
            }
            
            unset($paths[$ext->getId()][$index]);
            
            return true;
        } catch (\common_ext_ExtensionException $e) {
            return false;
        }
    }
    
    public function requirePos()
    {
        $count = 0;
        $extensionManager = \common_ext_ExtensionsManager::singleton();
        
        foreach ($this->getOption('paths') as $extId => $files) {
            $ext = $extensionManager->getExtensionById($extId);
            foreach ($files as $file) {
                if (\l10n::set($ext->getDir() . $file) !== false) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
}
