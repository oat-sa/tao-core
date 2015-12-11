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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\requiredAction\implementation;

use oat\tao\model\requiredAction\RequiredActionInterface;
use \Exception;
use oat\tao\model\requiredAction\RequiredActionRuleInterface;

/**
 * Class RequiredAction
 *
 * RequiredAction is action which should be executed by user before performing any activities in the TAO
 *
 * @package oat\tao\model\requiredAction\implementation
 * @author Aleh Hutnilau <hutnikau@1pt.com>
 */
class RequiredAction implements RequiredActionInterface
{

    private $name;

    /**
     * @var RequiredActionRuleInterface[]
     */
    private $rules = [];

    /**
     * @var callable
     */
    private $callback;

    /**
     * RequiredAction constructor.
     * @param string $name
     * @param callable $callback
     * @param RequiredActionRuleInterface[] $rules
     * @throws Exception
     */
    public function __construct($name, callable $callback, array $rules = [])
    {
        $this->name = $name;
        $this->callback = $callback;
        foreach ($rules as $rule) {
            $this->setRule($rule);
        }
    }

    /**
     * Execute an action
     * @param array $params params to be passed to callback function
     * @return mixed
     */
    public function execute(array $params = [])
    {
        return call_user_func_array($this->getCallback(), $params);
    }

    /**
     * Whether the action must be executed.
     * @return boolean
     */
    public function mustBeExecuted()
    {
        $result = $this->checkRules();

        return $result;
    }

    /**
     * Mark action as completed.
     * @return mixed
     */
    public function completed()
    {
        $rules = $this->getRules();
        foreach ($rules as $rule) {
            $rule->completed($this);
        }
    }

    /**
     * Get array of rules
     * @return RequiredActionRuleInterface[]
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Add rule to rules list
     * @param RequiredActionRuleInterface $rule
     * @return void
     */
    public function setRule(RequiredActionRuleInterface $rule)
    {
        $this->rules[] = $rule;
    }

    /**
     * Get action name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get callback to be executed in case if action must be performed.
     *
     * @return callable
     */
    private function getCallback()
    {
        return $this->callback;
    }

    /**
     * Check rules whether action must be performed.
     * If at least one rule returns true the action will be performed.
     * If result is `true` then action must be performed.
     * @return bool
     */
    private function checkRules()
    {
        $rules = $this->getRules();
        $result = false;

        foreach ($rules as $rule) {
            if ($rule->check()) {
                $result = true;
                break;
            }
        }

        return $result;
    }

}