<?php

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