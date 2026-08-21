<?php

/*
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
 *               2013-2014 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

/**
 * A TranslationExtractor that focuses on the extraction of Translation Units
 * source code. It searches for calls to the __() function. The generated
 * units will get the first parameter of the __() function as their source.
 *
 * @access public
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2

 * @version 1.0
 */
class tao_helpers_translation_SourceCodeExtractor extends tao_helpers_translation_TranslationExtractor
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute filesTypes
     *
     * @var array
     */
    private $filesTypes = [];

    /**
     *
     * @var array
     */
    private $bannedFileType = [ '.min.js' ];
    // --- OPERATIONS ---

    /**
     * Short description of method extract
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     */
    public function extract()
    {

        $this->setTranslationUnits([]);
        foreach ($this->getPaths() as $dir) {
            // Directories should come with a trailing slash.
            $d = strrev($dir);
            if ($d[0] !== '/') {
                $dir = $dir . '/';
            }

            $this->recursiveSearch($dir);
        }
    }

    /**
     * Short description of method recursiveSearch
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @param  string $directory
     */
    private function recursiveSearch($directory)
    {

        if (is_dir($directory)) {
            // We get the list of files and directories.
            if (($files = scandir($directory)) !== false) {
                foreach ($files as $fd) {
                    if (!preg_match("/^\./", $fd) &&  $fd != "ext") {
                        // If it is a directory ...
                        if (is_dir($directory . $fd . "/")) {
                            $this->recursiveSearch($directory . $fd . "/");
                        // If it is a file ...
                        } else {
                            // Retrieve from the file ...
                            $this->getTranslationsInFile($directory . $fd);
                        }
                    }
                }
            }
        }
    }

    /**
     * Creates a SourceCodeExtractor for a given set of paths. Only file
     * that matches an entry in the $fileTypes array will be processed.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  array $paths
     * @param  array $fileTypes
     * @return mixed
     */
    public function __construct($paths, array $fileTypes)
    {
        parent::__construct($paths);
        $this->setFileTypes($fileTypes);
    }

    /**
     * Gets an array of file extensions that will be processed. It acts as a
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return array
     */
    public function getFileTypes()
    {
        return $this->filesTypes;
    }

    /**
     * Sets an array that contains the extensions of files that have to be
     * during the invokation of the SourceCodeExtractor::extract method.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  array $fileTypes
     */
    public function setFileTypes(array $fileTypes)
    {
        $this->filesTypes = $fileTypes;
    }

    /**
     * Short description of method getTranslationsInFile
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @param  string $filePath
     */
    private function getTranslationsInFile($filePath)
    {

        // File extension ?
        $extOk = in_array(\Jig\Utils\FsUtils::getFileExtension($filePath), $this->getFileTypes());

        if ($extOk) {
            foreach ($this->getBannedFileType() as $bannedExt) {
                $extOk &= substr_compare(
                    $filePath,
                    $bannedExt,
                    strlen($filePath) - strlen($bannedExt),
                    strlen($bannedExt)
                ) !== 0;
            }
        }

        if ($extOk) {
            $content = file_get_contents($filePath);
            if ($content === false) {
                return;
            }

            $strings = $this->getTranslationPhrases($content);
            $pluralPhrases = $this->getPluralTranslationPhrases($content);

            foreach ($strings as $s) {
                $tu = new tao_helpers_translation_TranslationUnit();
                $tu->setSource(tao_helpers_translation_POUtils::sanitize($s));
                $this->addTranslationUnitIfMissing($tu);
            }

            foreach ($pluralPhrases as $pluralPhrase) {
                $tu = new tao_helpers_translation_POTranslationUnit();
                $tu->setSource(tao_helpers_translation_POUtils::sanitize($pluralPhrase['singular']));
                $tu->setSourcePlural(tao_helpers_translation_POUtils::sanitize($pluralPhrase['plural']));
                $this->addTranslationUnitIfMissing($tu);
            }
        }
    }

    /**
     * Add a translation unit only when an equivalent one does not already exist.
     *
     * @param tao_helpers_translation_TranslationUnit $translationUnit
     * @return void
     */
    private function addTranslationUnitIfMissing(tao_helpers_translation_TranslationUnit $translationUnit)
    {
        $translationUnits = $this->getTranslationUnits();

        foreach ($translationUnits as $existingTranslationUnit) {
            if ($this->isSameTranslationUnit($translationUnit, $existingTranslationUnit)) {
                return;
            }
        }

        $translationUnits[] = $translationUnit;
        $this->setTranslationUnits($translationUnits);
    }

    /**
     * @param tao_helpers_translation_TranslationUnit $left
     * @param tao_helpers_translation_TranslationUnit $right
     * @return bool
     */
    private function isSameTranslationUnit(
        tao_helpers_translation_TranslationUnit $left,
        tao_helpers_translation_TranslationUnit $right
    ) {
        if ($left->getSource() !== $right->getSource()) {
            return false;
        }

        if (
            $left instanceof tao_helpers_translation_POTranslationUnit
            && $right instanceof tao_helpers_translation_POTranslationUnit
        ) {
            return $left->getSourcePlural() === $right->getSourcePlural();
        }

        return !(
            $left instanceof tao_helpers_translation_POTranslationUnit
            || $right instanceof tao_helpers_translation_POTranslationUnit
        );
    }

    /**
     * @return array
     */
    public function getBannedFileType()
    {
        return $this->bannedFileType;
    }

    /**
     * @param array $bannedFileType
     */
    public function setBannedFileType($bannedFileType)
    {
        $this->bannedFileType = $bannedFileType;
    }

    /**
     * @param string $content
     *
     * @return array
     */
    protected function getTranslationPhrases($content)
    {
        $strings = [];

        foreach ($this->findAllMatches("/__\\(([\\\"'])(?:(?=(\\\\?))\\2.)*?\\1/us", $content) as $match) {
            $arguments = $this->extractQuotedArguments($match, 1);
            if (!empty($arguments)) {
                $strings[] = $arguments[0];
            }
        }

        $templateMatches = $this->findCapturedValues(
            "/\{\{\s*__\s+['\"](.*?)['\"]\s*\}\}/us",
            $content
        );
        if (!empty($templateMatches)) {
            $strings = array_merge($strings, $templateMatches);

            return $strings;
        }

        return $strings;
    }

    /**
     * @param string $content
     * @return array
     */
    protected function getPluralTranslationPhrases($content)
    {
        $strings = [];
        $extractors = [
            [
                'pattern' => "/__\\.p\\(\\s*([\\\"'])(?:(?=(\\\\?))\\2.)*?\\1\\s*,\\s*"
                    . "([\\\"'])(?:(?=(\\\\?))\\4.)*?\\3/us",
                'mode' => 'quotedArguments',
            ],
            [
                'pattern' => "/\\{\\{\s*__p\s+['\\\"](.*?)['\\\"]\s+['\\\"](.*?)['\\\"](?:\\s+.*?)?\\s*\\}\\}/us",
                'mode' => 'capturedGroups',
                'flags' => PREG_SET_ORDER,
            ],
        ];

        foreach ($extractors as $extractor) {
            $matches = $this->findAllMatches(
                $extractor['pattern'],
                $content,
                $extractor['flags'] ?? 0
            );
            foreach ($matches as $match) {
                $phrase = $extractor['mode'] === 'capturedGroups'
                    ? $this->buildPluralPhraseFromCapturedGroups($match)
                    : $this->buildPluralPhraseFromQuotedArguments($match);

                if ($phrase !== null) {
                    $strings[] = $phrase;
                }
            }
        }

        return $strings;
    }

    /**
     * @param string $pattern
     * @param string $content
     * @param int $flags
     * @return array
     */
    private function findAllMatches($pattern, $content, $flags = 0)
    {
        $matches = [];
        preg_match_all($pattern, $content, $matches, $flags);

        if ($flags === PREG_SET_ORDER) {
            return $matches;
        }

        return $matches[0] ?? [];
    }

    /**
     * @param string $pattern
     * @param string $content
     * @return array
     */
    private function findCapturedValues($pattern, $content)
    {
        $matches = [];
        preg_match_all($pattern, $content, $matches);

        return $matches[1] ?? [];
    }

    /**
     * @param string $content
     * @param int|null $limit
     * @return array
     */
    private function extractQuotedArguments($content, $limit = null)
    {
        $quotedArguments = [];
        preg_match_all(
            "/([\"'])(?:(?=(\\\\?))\\2.)*?\\1/u",
            $content,
            $quotedArguments
        );

        $arguments = array_map(
            function ($argument) {
                return trim($argument, '"\'');
            },
            $quotedArguments[0] ?? []
        );

        if ($limit !== null) {
            return array_slice($arguments, 0, $limit);
        }

        return $arguments;
    }

    /**
     * @param string $match
     * @return array|null
     */
    private function buildPluralPhraseFromQuotedArguments($match)
    {
        $quotedArguments = $this->extractQuotedArguments($match, 2);
        if (count($quotedArguments) < 2) {
            return null;
        }

        return [
            'singular' => $quotedArguments[0],
            'plural' => $quotedArguments[1],
        ];
    }

    /**
     * @param array $match
     * @return array|null
     */
    private function buildPluralPhraseFromCapturedGroups(array $match)
    {
        if (!isset($match[1], $match[2])) {
            return null;
        }

        return [
            'singular' => $match[1],
            'plural' => $match[2],
        ];
    }
}
