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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

/**
 * Class Security
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class tao_actions_Security extends tao_actions_CommonModule
{

    /**
     * Index page
     */
    public function index()
    {
        $this->setView('security/view.tpl');

        $formFactory = new tao_actions_form_CspHeader();
        $cspHeaderForm = $formFactory->getForm();
        $this->setData('formTitle', __('Edit sources that can embed this platform in an iFrame'));
        $this->setData('cspHeaderForm', $cspHeaderForm->render());
    }
}
