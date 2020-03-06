<?php declare(strict_types=1);

namespace oat\tao\test\Asset\helper\form;

use tao_helpers_form_FormContainer;
use tao_helpers_form_FormElement;
use tao_helpers_form_FormFactory;

class FormContainerStub extends tao_helpers_form_FormContainer
{
    /** @var tao_helpers_form_FormElement[] */
    private $elements;

    public function __construct($data = [], $options = [], tao_helpers_form_FormElement ...$elements)
    {
        $this->elements = $elements;

        parent::__construct($data, $options);
    }

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
        foreach ($this->elements as $element) {
            $this->form->addElement($element);
        }
    }
}
