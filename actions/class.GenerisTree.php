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
 * You should have received a copy of the GNU Genera Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *				 2013-2018 (update and modification) Open Assessment Technologies SA;
 */

/**
 * GenerisTree is a generic implementation of the
 * javascript generisTree getter and setter actions
 *
 * @author Joel bout, <joel@taotesting.com>
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 */
use oat\tao\model\Tree\GetTreeRequest;
use oat\tao\model\Tree\GetTreeService;
use oat\generis\model\OntologyAwareTrait;

class tao_actions_GenerisTree extends tao_actions_CommonModule
{
	use OntologyAwareTrait;

	const DEFAULT_LIMIT = 10;

	public function getData()
	{
		/** @var GetTreeService $service */
		$service = $this->getServiceLocator()->get(GetTreeService::SERVICE_ID);

		$response = $service->handle(GetTreeRequest::create($this->getRequest()));

		return $this->returnJson($response->getTreeArray());
	}

	public function setValues()
	{
		if (!$this->isXmlHttpRequest()) {
			throw new common_exception_IsAjaxAction(__FUNCTION__);
		}

		$values = tao_helpers_form_GenerisTreeForm::getSelectedInstancesFromPost();

		$resource = $this->getResource($this->getRequestParameter('resourceUri'));
		$property = $this->getProperty($this->getRequestParameter('propertyUri'));
		$success = $resource->editPropertyValues($property, $values);

        return $this->returnJson(array('saved'	=> $success ));
	}

	public function setReverseValues()
	{
		if (!$this->isXmlHttpRequest()) {
			throw new common_exception_IsAjaxAction(__FUNCTION__);
		}

		$values = tao_helpers_form_GenerisTreeForm::getSelectedInstancesFromPost();

		$resource = $this->getResource($this->getRequestParameter('resourceUri'));
		$property = $this->getProperty($this->getRequestParameter('propertyUri'));

		$currentValues = array();
		foreach ($property->getDomain() as $domain) {
			$instances = $domain->searchInstances(array(
				$property->getUri() => $resource
			), array('recursive' => true, 'like' => false));
			$currentValues = array_merge($currentValues, array_keys($instances));
		}

		$toAdd = array_diff($values, $currentValues);
		$toRemove = array_diff($currentValues, $values);

		$success = true;
		foreach ($toAdd as $uri) {
			$subject = $this->getResource($uri);
			$success = $success && $subject->setPropertyValue($property, $resource);
		}

		foreach ($toRemove as $uri) {
			$subject = $this->getResource($uri);
			$success = $success && $subject->removePropertyValue($property, $resource);
		}

        return $this->returnJson(array('saved'	=> $success));
	}
}