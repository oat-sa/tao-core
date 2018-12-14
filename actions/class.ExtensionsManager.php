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
<<<<<<< HEAD
 *               2013-2018 (update and modification) Open Assessment Technologies SA;
 */

use oat\tao\model\menu\MenuService;
use oat\tao\model\service\ApplicationService;

/**
 * Controller to manage extensions
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao

 *
 */
class tao_actions_ExtensionsManager extends tao_actions_CommonModule
{
    /**
     * Index page
     */
	public function index()
    {
        if ($this->isDebugMode() === true) {
            $isProduction = false;
            $availableExtArray = $this->getExtensionManager()->getAvailableExtensions();
            usort($availableExtArray, function($a, $b) { return strcasecmp($a->getId(),$b->getId());});
            $this->setData('availableExtArray', $availableExtArray);
        } else {
            $isProduction = true;
        }

		$this->setData('isProduction', $isProduction);
		$this->setData('installedExtArray', $this->getExtensionManager()->getInstalledExtensions());
		$this->setView('extensionManager/view.tpl');
	}

    /**
     * Return current extension
     *
     * @throws common_exception_MissingParameter
     * @throws common_ext_ExtensionException
     *
     * @return common_ext_Extension|null
     */
    protected function getCurrentExtension()
    {
		if ($this->hasRequestParameter('id')) {
			return $this->getExtensionManager()->getExtensionById($this->getRequestParameter('id'));
		} else {
            throw new common_exception_MissingParameter();
        }
	}

    /**
     * Install action
     *
     * @throws common_exception_BadRequest If platform is on production mode
     * @throws common_exception_Error
     */
    public function install()
    {
        $this->assertIsDebugMode();

		$success = false;
		try {
			$extInstaller = new tao_install_ExtensionInstaller($this->getCurrentExtension());
			$extInstaller->install();
			$message =   __('Extension "%s" has been installed', $this->getCurrentExtension()->getId());
			$success = true;

			// reinit user session
			$session = $this->getSession()->refresh();
		}
		catch(common_ext_ExtensionException $e) {
			$message = $e->getMessage();
		}

        $this->returnJson(array('success' => $success, 'message' => $message));
	}

    /**
     * Once some extensions have been installed, we trigger this action.
     *
     * @throws common_exception_BadRequest If platform is on production mode
     */
    public function postInstall()
    {
        $this->assertIsDebugMode();

        $success = true;
        $message = '';

        // try to regenerate languages bundles
        try {
            tao_models_classes_LanguageService::singleton()->generateAll(true);
        } catch (common_exception_Error $e) {
            $message = $e->getMessage();
            $success = false;
        }

        $this->returnJson(array(
            'success' => $success,
            'message' => $message
        ));
    }

	/**
	 * Disable an extension
     *
     * @throws common_exception_BadRequest If platform is on production mode
     * @throws common_exception_Error
	 */
	public function disable()
    {
        $this->assertIsDebugMode();

	    $extId = $this->getRequestParameter('id');
        $this->getExtensionManager()->setEnabled($extId, false);
	    MenuService::flushCache();
        $this->returnJson(array(
	        'success' => true,
	        'message' => __('Disabled %s', $this->getRequestParameter('id'))
	    ));
	}

    /**
     *  Enable an extension
     *
     * @throws common_exception_BadRequest If platform is on production mode
     * @throws common_exception_Error
     */
	public function enable()
    {
        $this->assertIsDebugMode();

	    $extId = $this->getRequestParameter('id');
        $this->getExtensionManager()->setEnabled($extId, true);
	    MenuService::flushCache();
        $this->returnJson(array(
	        'success' => true,
	        'message' => __('Enabled %s', $this->getRequestParameter('id'))
	    ));
	}

    /**
     * Uninstall an extension
     *
     * @throws common_exception_BadRequest If platform is on production mode
     */
	public function uninstall()
    {
        $this->assertIsDebugMode();
	    try {
	        $uninstaller = new \tao_install_ExtensionUninstaller($this->getCurrentExtension());
	        $success = $uninstaller->uninstall();
	        $message = __('Uninstalled %s', $this->getRequestParameter('id'));
	    } catch (\common_Exception $e) {
	        $success = false;
	        if ($e instanceof \common_exception_UserReadableException) {
	            $message = $e->getUserMessage();
	        } else {
	            $message = __('Uninstall of %s failed', $this->getRequestParameter('id'));
	        }
	    }
        $this->returnJson(array(
	        'success' => $success,
	        'message' => $message
	    ));
	}

    /**
     * Throw a bad request exception if the platform is on production mode
     *
     * @throws common_exception_BadRequest
     */
	protected function assertIsDebugMode()
    {
        if ($this->isDebugMode() !== true) {
            throw new common_exception_BadRequest('This operation cannot be processed in production mode.');
        }
    }

    /**
     * Check if the platform is on debug mode
     * @return bool
     */
    protected function isDebugMode()
    {
        return $this->getServiceLocator()->get(ApplicationService::SERVICE_ID)->isDebugMode();
    }

    /**
     * Get the common_ext_ExtensionsManager service
     *
     * @return common_ext_ExtensionsManager
     */
    protected function getExtensionManager()
    {
        return $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
    }

}
