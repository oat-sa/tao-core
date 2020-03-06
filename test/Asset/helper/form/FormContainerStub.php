<?php declare(strict_types=1);

namespace oat\tao\test\Asset\helper\form;

use tao_helpers_form_FormContainer;
use tao_helpers_form_FormFactory;

class FormContainerStub extends tao_helpers_form_FormContainer
{
    /**
     * @inheritDoc
     */
    protected function initForm(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->form = tao_helpers_form_FormFactory::getForm('test', $this->options);
    }

    /**
     * @inheritDoc
     */
    protected function initElements(): void
    {
        // NOOP
    }
}
