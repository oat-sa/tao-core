<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace oat\tao\model\mvc\view;
/**
 * Description of ViewHelperAbstract
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
abstract class ViewHelperAbstract implements ViewHelperInterface 
{
    /**
     * context variable
     * @var array
     */
    protected $context = [];
    
    /**
     * set context
     * @param array $context
     * @return $this
     */
    public function setContext(array $context) {
        $this->context = $context;
        return $this;
    }
    
    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        if(array_key_exists($name, $this->context)) {
            return $this->context[$name];
        }
        return null;
    }
    
}
