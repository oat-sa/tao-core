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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * @author Mikhail Kamarouski, <kamarouski@1pt.com>
 */
class tao_install_checks_OpcacheLoadComments extends common_configuration_Component
{

    /**
     * @return common_configuration_Report
     */
    public function check()
    {
        if (version_compare(phpversion(), '7.0.0', '>=')) {
            return new common_configuration_Report(common_configuration_Report::VALID, 'opcache.load_comments is not a configurable option any more for php > 7', $this);
        }

        $iniSettingCheck = new common_configuration_PHPINIValue('1', 'opcache.load_comments');
        $result = $iniSettingCheck->check();
        $result->setComponent($this);
        return $result;
    }
}