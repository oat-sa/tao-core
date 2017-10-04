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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model;

interface WfEngineOntology
{
	const CLASS_URI_CALL_OF_SERVICES = 'http://www.tao.lu/middleware/wfEngine.rdf#ClassCallOfServices';
	const PROPERTY_CALL_OF_SERVICES_SERVICE_DEFINITION = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesServiceDefinition';
	const PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_OUT = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesActualParameterOut';
	const PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_IN = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesActualParameterin';
	const PROPERTY_CALL_OF_SERVICES_TOP = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesTop';
	const PROPERTY_CALL_OF_SERVICES_LEFT = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesLeft';
	const PROPERTY_CALL_OF_SERVICES_WIDTH = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesWidth';
	const PROPERTY_CALL_OF_SERVICES_HEIGHT = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesHeight';
	const CLASS_URI_ACTUAL_PARAMETER = 'http://www.tao.lu/middleware/wfEngine.rdf#ClassActualParameters';
	const PROPERTY_ACTUAL_PARAMETER_PROCESS_VARIABLE = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersProcessVariable';
	const PROPERTY_ACTUAL_PARAMETER_CONSTANT_VALUE = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersConstantValue';
	const PROPERTY_ACTUAL_PARAMETER_FORMAL_PARAMETER = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersFormalParameter';
	const CLASS_URI_SERVICES_DEFINITION = 'http://www.tao.lu/middleware/wfEngine.rdf#ClassServiceDefinitions';
	const PROPERTY_SERVICES_DEFINITION_FORMAL_PARAM_OUT = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyServiceDefinitionsFormalParameterOut';
	const PROPERTY_SERVICES_DEFINITION_FORMAL_PARAM_IN = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyServiceDefinitionsFormalParameterIn';
	const CLASS_URI_SUPPORT_SERVICES = 'http://www.tao.lu/middleware/wfEngine.rdf#ClassSupportServices';
	const PROPERTY_SUPPORT_SERVICES_URL = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertySupportServicesUrl';
	const CLASS_URI_WEBSERVICES = 'http://www.tao.lu/middleware/wfEngine.rdf#ClassWebServices';
	const CLASS_URI_FORMAL_PARAMETER = 'http://www.tao.lu/middleware/wfEngine.rdf#ClassFormalParameters';
	const PROPERTY_FORMAL_PARAMETER_DEFAULTCONSTANT_VALUE = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersDefaultConstantValue';
	const PROPERTY_FORMAL_PARAMETER_DEFAULT_PROCESS_VARIABLE = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersDefaultProcessVariable';
	const PROPERTY_FORMAL_PARAMETER_NAME = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersName';
}