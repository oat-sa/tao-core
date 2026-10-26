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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA ;
 */
define([], function () {
    'use strict';

    const ERROR_INVALID_CONFIG = 'PROCESS_BLOCK_LIST_INVALID_CONFIG';
    const ERROR_API_UNAVAILABLE = 'PROCESS_BLOCK_LIST_API_UNAVAILABLE';
    const ERROR_API_TIMEOUT = 'PROCESS_BLOCK_LIST_API_TIMEOUT';
    const ERROR_API_REJECTED = 'PROCESS_BLOCK_LIST_API_REJECTED';
    const ERROR_INVALID_RESPONSE = 'PROCESS_BLOCK_LIST_INVALID_RESPONSE';
    const normalizedByProcesses = new WeakMap();

    function createError(code, message, cause) {
        const error = new Error(message);

        error.code = code;

        if (cause) {
            error.cause = cause;
        }

        return error;
    }

    function normalizeRequiredString(value) {
        return typeof value === 'string' ? value.trim() : '';
    }

    function create(processes) {
        const vendorMap = {};
        const labelsByProcess = Object.create(null);
        const labels = Object.create(null);

        if (!Array.isArray(processes)) {
            throw createError(ERROR_INVALID_CONFIG, 'The process block list must be an array');
        }

        processes.forEach(function (entry) {
            const process = normalizeRequiredString(entry && entry.process);
            const label = normalizeRequiredString(entry && entry.label);
            const key = process.toLowerCase();

            if (!entry || typeof entry !== 'object' || Array.isArray(entry) || !process || !label) {
                throw createError(
                    ERROR_INVALID_CONFIG,
                    'Each process block list entry must contain a process and label'
                );
            }

            if (Object.prototype.hasOwnProperty.call(labelsByProcess, key)) {
                throw createError(ERROR_INVALID_CONFIG, 'Process block list entries must be unique');
            }

            vendorMap[key] = label;
            labelsByProcess[key] = label;
            labels[label.toLowerCase()] = label;
        });

        return {
            vendorMap: vendorMap,

            resolveBlockedApplications: function resolveBlockedApplications(applications) {
                if (!Array.isArray(applications)) {
                    throw createError(ERROR_INVALID_RESPONSE, 'The process block list API must return an array');
                }

                return applications.map(function (application) {
                    const value = normalizeRequiredString(application);
                    const key = value.toLowerCase();

                    if (!value) {
                        throw createError(
                            ERROR_INVALID_RESPONSE,
                            'The process block list API returned an invalid application'
                        );
                    }

                    return labelsByProcess[key] || labels[key] || value;
                });
            },

            getLabel: function getLabel(processName) {
                const key = normalizeRequiredString(processName).toLowerCase();

                return labelsByProcess[key];
            }
        };
    }

    function normalize(processes) {
        let normalized;

        if (
            processes &&
            processes.vendorMap &&
            typeof processes.resolveBlockedApplications === 'function' &&
            typeof processes.getLabel === 'function'
        ) {
            return processes;
        }

        if (!Array.isArray(processes)) {
            return create(processes);
        }

        normalized = normalizedByProcesses.get(processes);

        if (!normalized) {
            normalized = create(processes);
            normalizedByProcesses.set(processes, normalized);
        }

        return normalized;
    }

    return {
        ERROR_INVALID_CONFIG: ERROR_INVALID_CONFIG,
        ERROR_API_UNAVAILABLE: ERROR_API_UNAVAILABLE,
        ERROR_API_TIMEOUT: ERROR_API_TIMEOUT,
        ERROR_API_REJECTED: ERROR_API_REJECTED,
        ERROR_INVALID_RESPONSE: ERROR_INVALID_RESPONSE,
        createError: createError,
        create: create,
        normalize: normalize
    };
});
