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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA
 *
 */
namespace oat\tao\model\event;

class RoleChangedEvent extends AbstractRoleEvent
{
    protected $essence;
    protected $changes;

    public function jsonSerialize()
    {
        return [
            'roleUri' => $this->roleUri,
            'essence' => $this->essence,
            'changes' => $this->changes,
        ];
    }

    /**
     * RoleChangedEvent constructor.
     * @param string $roleUri
     * @param string $essence
     * @param mixed null $changes
     */
    public function __construct($roleUri, $essence, $changes = null)
    {
        parent::__construct($roleUri);
        $this->essence = $essence;
        $this->changes = $changes;
    }


}
