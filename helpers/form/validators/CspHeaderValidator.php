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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\helpers\form\validators;

/**
 * Validates the given CSP headers
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class CspHeaderValidator extends \tao_helpers_form_Validator
{

    const DIRECTIVES = [
        'self',
        'none',
        '*'
    ];

    /**
     * @var string[][]
     */
    private $invalidValues;

    /**
     * Overrides parent default message
     *
     * @return string
     */
    protected function getDefaultMessage()
    {
        return __('Invalid CSP header.');
    }

    /**
     * Validates the list of domains and directives for the CSP Header.
     *
     * @param string $values
     * @return bool
     */
    public function evaluate($values)
    {
        // Only validate if the source is set to 'list'
        $sourceElement = $this->getOption('sourceElement');
        $sourceElementValue = $sourceElement->getEvaluatedValue();
        if ($sourceElementValue !== 'list') {
            return true;
        }

        $this->invalidValues = [];
        $values = trim(str_replace("\r", '', $values));

        if (!$values) {
            $this->setMessage('Please add at least one domain or directive.');
            return false;
        }

        $sources = explode("\n", $values);

        foreach ($sources as $key => $source) {
            if ($source === '') {
                unset($sources[$key]);
            }

            if (in_array($source, self::DIRECTIVES, true)) {
                if ($this->isValidDirective($source) === false) {
                    $this->invalidValues['domain'][] = $source;
                }
                $sources[$key] = $this->getNormalizedDirective($source);

                continue;
            }

            if ($this->isValidDomain($source) === false) {
                $this->invalidValues['domain'][] = $source;
            }
        }

        $isValid = empty($this->invalidValues);
        if (!$isValid) {
            $this->setMessage($this->getErrorMessage());
        }

        return $isValid;
    }

    /**
     * Check if the given directive need to be converted.
     *
     * @param string $directive
     * @return string
     */
    private function getNormalizedDirective($directive)
    {
        $directive = strtolower($directive);

        if (ctype_alpha($directive) === true) {
            $directive = "'" . $directive . "'";
        }

        return $directive;
    }

    /**
     * Check if the given directive is valid
     *
     * @param string $directive
     * @return bool
     */
    private function isValidDirective($directive)
    {
        if ($directive === '*') {
            return true;
        }
        return preg_match('/^(\'[a-z]+\'|[a-z]+)$/i', $directive) !== false;
    }

    /**
     * Check if the given domain is valid.
     *
     * @param string $domain
     * @return bool
     */
    private function isValidDomain($domain)
    {
        if (filter_var($domain, FILTER_VALIDATE_URL)) {
            return true;
        }

        $regex = '~^(https?:\/\/|(\*\.){1})?(\w.+)(\.)(?!\s)(?!\.\*)(\w{2,})$~i';
        return (bool) preg_match($regex, $domain);
    }

    /**
     * Get the error messages.
     */
    private function getErrorMessage()
    {
        $directivesMessage = '';
        $domainsMessage = '';

        if (!empty($this->invalidValues['directives'])) {
            $directivesMessage = "The following directives are invalid:\n- ";
            $directivesMessage .= implode("\n- ", $this->invalidValues['directives']);
        }

        if (!empty($this->invalidValues['domain'])) {
            $domainsMessage = "The following domains are invalid:\n- ";
            $domainsMessage .= implode("\n- ", $this->invalidValues['domain']);
        }

        return $domainsMessage . "\n" . $directivesMessage;
    }
}
