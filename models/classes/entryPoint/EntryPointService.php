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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\model\entryPoint;

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManager;

/**
 * 
 * Registry to store client library paths that will be provide to requireJs
 *
 * @author Lionel Lecaque, lionel@taotesting.com
 */
class EntryPointService extends ConfigurableService
{
    const SERVICE_ID = 'tao/entrypoint';
    
    const OPTION_ENTRYPOINTS = 'existing';
    
    const OPTION_PRELOGIN = 'prelogin';
    
    const OPTION_POSTLOGIN = 'postlogin';

    /**
     * Replace the entrypoint with the id provided
     * 
     * @param string $id
     * @param Entrypoint $e
     */
    public function overrideEntryPoint($id, Entrypoint $e)
    {
        $entryPoints = $this->getOption(self::OPTION_ENTRYPOINTS);
        $entryPoints[$id] = $e;
        $this->setOption(self::OPTION_ENTRYPOINTS, $entryPoints);
    }
    
    /**
     * Activate an existing entry point for a specific target
     * 
     * @param string $entryId
     * @param string $target
     * @throws \common_exception_InconsistentData
     * @return boolean success
     */
    public function activateEntryPoint($entryId, $target)
    {
        $success = false;
        $entryPoints = $this->getOption(self::OPTION_ENTRYPOINTS);
        if (!isset($entryPoints[$entryId])) {
            throw new \common_exception_InconsistentData('Unknown entrypoint '.$entryId);
        }
        $actives = $this->hasOption($target) ? $this->getOption($target) : array();
        if (!in_array($entryId, $actives)) {
            $actives[] = $entryId;
            $this->setOption($target, $actives);
            $success = true;
        }

        return $success;
    }
    
    /**
     * Dectivate an existing entry point for a specific target
     *
     * @param string $entryId
     * @param string $target
     * @throws \common_exception_InconsistentData
     * @return boolean success
     */
    public function deactivateEntryPoint($entryId, $target = self::OPTION_POSTLOGIN)
    {
        $success = false;
        $entryPoints = $this->getOption(self::OPTION_ENTRYPOINTS);
        if (!isset($entryPoints[$entryId])) {
            throw new \common_exception_InconsistentData('Unknown entrypoint '.$entryId);
        }
        $actives = $this->hasOption($target) ? $this->getOption($target) : array();
        if (in_array($entryId, $actives)) {
            $actives = array_diff($actives, array($entryId));
            $this->setOption($target, $actives);
            $success = true;
        } else {
            \common_Logger::w('Tried to desactivate inactive entry point '.$entryId);
        }
        return $success;
    }
    
    
    /**
     * Add an Entrypoint and activate it if a target is specified
     * 
     * @param Entrypoint $e
     * @param string $target
     */
    public function addEntryPoint(Entrypoint $e, $target = null)
    {
        $entryPoints = $this->getOption(self::OPTION_ENTRYPOINTS);
        $entryPoints[$e->getId()] = $e;
        $this->setOption(self::OPTION_ENTRYPOINTS, $entryPoints);

        if (!is_null($target)) {
            $this->activateEntryPoint($e->getId(), $target);
        }
    }

    /**
     * Remove entrypoint
     *
     * @param $entryId
     * @throws \common_exception_InconsistentData
     */
    public function removeEntryPoint($entryId)
    {
        $entryPoints = $this->getOption(self::OPTION_ENTRYPOINTS);
        if (!isset($entryPoints[$entryId])) {
            throw new \common_exception_InconsistentData('Unknown entrypoint ' . $entryId);
        }

        // delete entrypoint from all options entries
        $options = $this->getOptions();
        foreach ($options as $section => $option) {
            $sectionIsArray = false;
            foreach ($option as $key => $val) {
                if ($key === $entryId || $val == $entryId) {
                    unset($options[$section][$key]);
                }

                if ($val == $entryId) {
                    $sectionIsArray = true;
                }
            }

            if ($sectionIsArray) {
                $options[$section] = array_values($options[$section]);
            }
        }

        $this->setOptions($options);
    }

    /**
     * Get all entrypoints for a designated target
     * 
     * @param string $target
     * @return Entrypoint[]
     */
    public function getEntryPoints($target = self::OPTION_POSTLOGIN)
    {
        $ids = $this->hasOption($target) ? $this->getOption($target) : array();
        $existing = $this->getOption(self::OPTION_ENTRYPOINTS);

        if ($target === self::OPTION_ENTRYPOINTS) {
            return $existing;
        }

        $entryPoints = array();
        foreach ($ids as $id) {
            $entryPoints[$id] = $existing[$id];
        }
        return $entryPoints;
    }

    /**
     * Legacy function for backward compatibilitiy
     * 
     * @return EntryPointService
     * @deprecated
     */
    public static function getRegistry()
    {
        return ServiceManager::getServiceManager()->get(self::SERVICE_ID);
    }
    
    /**
     * Legacy function for backward compatibilitiy
     * 
     * @param Entrypoint $e
     * @param string $target
     * @deprecated
     */
    public function registerEntryPoint(Entrypoint $e)
    {
        $this->addEntryPoint($e, self::OPTION_POSTLOGIN);
        $this->getServiceManager()->register(self::SERVICE_ID, $this);
    }
}
