<?php

error_reporting(E_ALL);

/**
 * A PO Translation Unit.
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * A Translation Unit represents a single unit of translation of a software,
 * file, ... It has a source text in the original language and a target in which
 * text has to be translated.
 *
 * Example:
 * Source (English): The end is far away
 * Target (Yoda English): Far away the end is
 *
 * @author Jerome Bogaerts
 * @since 2.2
 * @version 1.0
 */
require_once('tao/helpers/translation/class.TranslationUnit.php');

/* user defined includes */
// section -64--88-56-1--6ccfbacb:137c11aa2dd:-8000:0000000000003AC5-includes begin
// section -64--88-56-1--6ccfbacb:137c11aa2dd:-8000:0000000000003AC5-includes end

/* user defined constants */
// section -64--88-56-1--6ccfbacb:137c11aa2dd:-8000:0000000000003AC5-constants begin
// section -64--88-56-1--6ccfbacb:137c11aa2dd:-8000:0000000000003AC5-constants end

/**
 * A PO Translation Unit.
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */
class tao_helpers_translation_POTranslationUnit
    extends tao_helpers_translation_TranslationUnit
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Annotation identifier for PO translator comments.
     *
     * @access public
     * @var string
     */
    const TRANSLATOR_COMMENTS = 'po-translator-comments';

    /**
     * Annotation identifier for PO extracted comments.
     *
     * @access public
     * @var string
     */
    const EXTRACTED_COMMENTS = 'po-extracted-comments';

    /**
     * Annotation identifier for PO message flags.
     *
     * @access public
     * @var string
     */
    const FLAGS = 'po-flags';

    /**
     * Annotation identifier for PO reference flag.
     *
     * @access public
     * @var string
     */
    const REFERENCE = 'po-reference';

    /**
     * Annotation identifier for the previous translation PO comment.
     *
     * @access public
     * @var string
     */
    const PREVIOUS_TRANSLATION = 'po-previous-translation';

    /**
     * Annotation identifier for the PO previous translation (singular) comment.
     *
     * @access public
     * @var string
     */
    const PREVIOUS_TRANSLATION_SINGULAR = 'po-previous-translation-singular';

    /**
     * Annotation identifier for the PO previous translation (plural) comment.
     *
     * @access public
     * @var string
     */
    const PREVIOUS_TRANSLATION_PLURAL = 'po-previous-translation-plural';

    /**
     * Annotation identifier for the message context comment.
     *
     * @access public
     * @var string
     */
    const MESSAGE_CONTEXT = 'po-message-context';

    // --- OPERATIONS ---

} /* end of class tao_helpers_translation_POTranslationUnit */

?>