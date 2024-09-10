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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\form\Modifier;

use InvalidArgumentException;
use tao_helpers_form_Form as Form;

class FormModifierManager
{
    public const OPTIONS_IDS = 'ids';

    /** @var FormModifierInterface[] */
    private array $extenders = [];

    public function add(FormModifierInterface $extender, string $id): void
    {
        if (array_key_exists($id, $this->extenders)) {
            throw new InvalidArgumentException(sprintf('Form extender with id "%s" already exists.', $id));
        }

        $this->extenders[$id] = $extender;
    }

    public function modify(Form $form, array $options = []): void
    {
        $ids = $options[self::OPTIONS_IDS] ?? [];

        $extenders = !empty($ids)
            ? array_intersect_key($this->extenders, array_flip($ids))
            : $this->extenders;

        foreach ($extenders as $extender) {
            if ($extender->supports($form, $options)) {
                $extender->modify($form, $options);
            }
        }
    }
}
