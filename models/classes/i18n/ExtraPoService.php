<?php

namespace oat\tao\model\i18n;

use common_ext_ExtensionsManager as ExtensionsManager;
use l10n;
use oat\oatbox\service\ConfigurableService;

// todo: two of methods are not used, check if this service needed?
class ExtraPoService extends ConfigurableService
{
    public const SERVICE_ID = 'tao/ExtraPoService';

    protected const OPTION_PATHS = 'paths';
    
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
    
    public function requirePOFiles(): void
    {
        $extensionManager = $this->getServiceLocator()->get(ExtensionsManager::SERVICE_ID);

        foreach ($this->getOption(self::OPTION_PATHS) as $id => $files) {
            $extension = $extensionManager->getExtensionById($id);
            foreach ($files as $file) {
                l10n::set($extension->getDir() . $file);
            }
        }
    }
}
