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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Validation;

use InvalidArgumentException;
use oat\oatbox\validator\ValidatorInterface;
use oat\tao\helpers\form\validators\CrossElementEvaluationAware;
use tao_helpers_form_Form;

class PropertyTypeValidator implements ValidatorInterface, CrossElementEvaluationAware
{
//    /** @var Ontology */
//    private $ontology;
//
//    public function __construct(Ontology $ontology)
//    {
//        $this->ontology = $ontology;
//    }

//    public function validate()
//    {
//        //@TODO Define validation rules
//
//        // 1) Is child or parent, cannot change List Values
//        // 2) Is child or parent, must respect restrict types
//    }

    /** @var array */
    private $options = [];

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return __('Some error here...'); //FIXME Use proper message
    }

    protected function getDefaultMessage()
    {
        return __('Some error here (default)...');
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        throw new InvalidArgumentException(
            sprintf(
                'Message for validator %s cannot be set.',
                self::class
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function evaluate($values)
    {
        return true; //FIXME Change to false and it crashes the application
    }

    public function acknowledge(tao_helpers_form_Form $form): void
    {
        //$property = $this->ontology->getProperty(tao_helpers_Uri::decode($this->element->getName()));
        $form;
        //FIXME Prepare validation
    }
}
