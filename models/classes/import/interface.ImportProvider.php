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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *               
 * 
 */

/**
 * interface implemented by itemModels that provide
 * an import 
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package taoItems
 
 */
interface tao_models_classes_import_ImportProvider
{


    // --- OPERATIONS ---

    /**
     * Gets the import handlers that can be used to import
     * resources of this type
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     * @return array
     */
    public function getImportHandlers();

}
