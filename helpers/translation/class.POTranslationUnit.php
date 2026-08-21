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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

/**
 * A PO Translation Unit.
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao

 */
class tao_helpers_translation_POTranslationUnit extends tao_helpers_translation_TranslationUnit
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Annotation identifier for PO translator comments.
     *
     * @access public
     * @var string
     */
    public const TRANSLATOR_COMMENTS = 'po-translator-comments';

    /**
     * Annotation identifier for PO extracted comments.
     *
     * @access public
     * @var string
     */
    public const EXTRACTED_COMMENTS = 'po-extracted-comments';

    /**
     * Annotation identifier for PO message flags.
     *
     * @access public
     * @var string
     */
    public const FLAGS = 'po-flags';

    /**
     * Annotation identifier for PO reference flag.
     *
     * @access public
     * @var string
     */
    public const REFERENCE = 'po-reference';

    /**
     * Annotation identifier for the PO previous untranslated string (singular)
     *
     * @access public
     * @var string
     */
    public const PREVIOUS_MSGID = 'po-previous-msgid';

    /**
     * Annotation identifier for the PO previous untranslated string (plural)
     *
     * @access public
     * @var string
     */
    public const PREVIOUS_MSGID_PLURAL = 'po-previous-msgid-plural';

    /**
     * Annotation identifier for the message context comment.
     *
     * @access public
     * @var string
     */
    public const PREVIOUS_MSGCTXT = 'po-previous-msgctxt';

    /**
     * Plural source text for PO plural entries.
     *
     * @var string
     */
    private $sourcePlural = '';

    /**
     * Indexed plural targets for PO plural entries.
     *
     * @var array
     */
    private $targets = [];

    // --- OPERATIONS ---

    /**
     * Add a PO compliant flag to the TranslationUnit. The FLAGS annotation will
     * created if no flags were added before.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string flag A flag string.
     * @return void
     */
    public function addFlag($flag)
    {

        $currentAnnotations = $this->getAnnotations();
        if (!isset($currentAnnotations[self::FLAGS])) {
            $currentAnnotations[self::FLAGS] = $flag;
        } elseif (!(mb_strpos($currentAnnotations[self::FLAGS], $flag, 0, TAO_DEFAULT_ENCODING) !== false)) {
            $currentAnnotations[self::FLAGS] .= " {$flag}";
        }

        $this->setAnnotations($currentAnnotations);
    }

    /**
     * Remove a given PO compliant flag from the TranslationUnit. The FLAGS
     * will be removed from the TranslationUnit if it was the last one of the
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string flag A flag string.
     * @return void
     */
    public function removeFlag($flag)
    {

        $currentFlags = $this->getFlags();
        for ($i = 0; $i < count($currentFlags); $i++) {
            if ($currentFlags[$i] == $flag) {
                break;
            }
        }

        if ($i <= count($currentFlags)) {
            // The flag is found.
            unset($currentFlags[$i]);
            $this->setFlags($currentFlags);
        }
    }

    /**
     * Short description of method hasFlag
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string flag A PO flag string.
     * @return boolean
     */
    public function hasFlag($flag)
    {
        $returnValue = (bool) false;


        foreach ($this->getFlags() as $f) {
            if ($f == $flag) {
                $returnValue = true;
                break;
            }
        }


        return (bool) $returnValue;
    }

    /**
     * Get the flags associated to the TranslationUnit. If there are no flags,
     * empty array is returned. Otherwise, a collection of strings is returned.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return array
     */
    public function getFlags()
    {
        $returnValue = [];


        $currentAnnotations = $this->getAnnotations();
        if (isset($currentAnnotations[self::FLAGS])) {
            $returnValue = explode(" ", $currentAnnotations[self::FLAGS]);
        }


        return (array) $returnValue;
    }

    /**
     * Associate a collection of PO flags to the TranslationUnit. A FLAGS
     * will be added to the TranslationUnit will be added consequently to the
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  array flags An array of PO string flags.
     */
    public function setFlags($flags)
    {

        $currentAnnotations = $this->getAnnotations();
        $currentAnnotations[self::FLAGS] = implode(" ", $flags);
        $this->setAnnotations($currentAnnotations);
    }

    /**
     * @return string
     */
    public function getSourcePlural()
    {
        return $this->sourcePlural;
    }

    /**
     * @param string $sourcePlural
     */
    public function setSourcePlural($sourcePlural)
    {
        $this->sourcePlural = $sourcePlural;
    }

    /**
     * @return bool
     */
    public function hasPluralSource()
    {
        return $this->sourcePlural !== '';
    }

    /**
     * @return array
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * @param array $targets
     */
    public function setTargets(array $targets)
    {
        ksort($targets);
        $this->targets = $targets;

        if (array_key_exists(0, $targets)) {
            parent::setTarget($targets[0]);
        }
    }

    /**
     * @param int $index
     * @param string $target
     */
    public function setTargetByIndex($index, $target)
    {
        $this->targets[(int) $index] = $target;
        ksort($this->targets);

        if ((int) $index === 0) {
            parent::setTarget($target);
        }
    }

    /**
     * @param int $index
     * @return string
     */
    public function getTargetByIndex($index)
    {
        return $this->targets[(int) $index] ?? '';
    }

    /**
     * @return bool
     */
    public function hasPluralTargets()
    {
        return !empty($this->targets);
    }

    /**
     * Include plural metadata so plural round-trips are comparable in tests.
     *
     * @return string
     */
    public function __toString()
    {
        $serializedTargets = [];
        foreach ($this->targets as $index => $target) {
            $serializedTargets[] = $index . ':' . $target;
        }

        return parent::__toString()
            . '|plural:' . $this->sourcePlural
            . '|targets:' . implode(',', $serializedTargets);
    }
}
