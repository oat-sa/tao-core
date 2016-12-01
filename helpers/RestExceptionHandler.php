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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\helpers;

use Exception;

class RestExceptionHandler
{

	/**
	 * Set response header according exception type
	 * @param Exception $exception
	 */
    public function sendHeader(Exception $exception)
	{
		switch (get_class($exception)) {

			case \common_exception_BadRequest::class:
			case \common_exception_MissingParameter::class:
			case \common_exception_InvalidArgumentType::class:
			case \common_exception_InconsistentData::class:
			case \common_exception_ValidationFailed::class:
				return 400;
				break;

			case \common_exception_Unauthorized::class:
				return 401;
				break;

			case \common_exception_NotFound::class:
				return 404;
				break;

            case \common_exception_MethodNotAllowed::class:
				return 405;
				break;

			case \common_exception_NotAcceptable::class:
				return 406;
				break;

			case "common_exception_TimeOut":
				return 408;
				break;

			case "common_exception_Conflict":
				return 409;
				break;

			case "common_exception_UnsupportedMediaType":
				return 415;
				break;

			case \common_exception_NotImplemented::class:
				return 501;
				break;

			case \common_exception_PreConditionFailure::class:
				return 412;
				break;

			case \common_exception_NoContent::class:
				return 204;
				break;

			case "common_exception_teapotAprilFirst":
				return 418;
				break;

			default:
				return 500;

		}
    }
}
