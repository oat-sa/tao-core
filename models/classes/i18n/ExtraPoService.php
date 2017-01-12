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
    
    public function addPoPath($path)
    {
        $paths = $this->getOption('paths');
        
        if (in_array($path, $paths) === false) {
            $paths[] = $path;
        }
        
        $this->setOption('paths', $paths);
    }
    
    public function removePoPath($path)
    {
        $paths = $this->getOption('paths');
        $index = array_search($path, $paths);
        
        if ($index === false) {
            return $index;
        }
        
        unset($paths[$index]);
        
        return true;
    }
    
    public function requirePos()
    {
        $count = 0;
        
        foreach ($this->getOption('paths') as $path) {
            if (l10n::set($path) !== false) {
                $count++;
            }
        }
        
        return $count;
    }
}
