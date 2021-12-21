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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

use oat\tao\model\routing\Contract\ActionInterface;

class tao_actions_MetadataImport implements ActionInterface
{
    use \oat\tao\model\mvc\RendererTrait;

    public function index(): void
    {
        //FIXME
        //FIXME
        //FIXME
        //FIXME
        $this->setView('security/view.tpl');

        $formFactory = new tao_actions_form_CspHeader(
            [tao_actions_form_CspHeader::SETTINGS_DATA => []]
        );

        $form = $formFactory->getForm();

        if ($form->isSubmited() && $form->isValid()) {
            $this->setData('cspHeaderFormSuccess', __('CSP Header settings were saved successfully!'));
        }

        $this->setData('cspHeaderForm', $form->render());
    }
}
