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
 * An implementation of TranslationFileReader aiming at reading PO files.
 *
 * @access public
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2

 * @version 1.0
 */
class tao_helpers_translation_POFileReader extends tao_helpers_translation_TranslationFileReader
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method read
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @throws tao_helpers_translation_TranslationException
     * @return mixed
     */
    public function read()
    {

        $file = $this->getFilePath();
        if (!file_exists($file)) {
            throw new tao_helpers_translation_TranslationException("The translation file '{$file}' does not exist.");
        }

        // Create the translation file.
        $tf = new tao_helpers_translation_POFile();

        $fc = implode('', file($file));

        $entries = preg_split("/(?:\r?\n){2,}/", trim($fc));
        foreach ($entries as $entry) {
            $parsedEntry = $this->parseEntry($entry);
            if ($parsedEntry === null) {
                continue;
            }

            if ($parsedEntry['msgid'] === '') {
                $headers = $this->extractHeaders($parsedEntry['msgstr']);
                foreach ($headers as $name => $value) {
                    $tf->addHeader($name, $value);
                }
                continue;
            }

            $tu = new tao_helpers_translation_POTranslationUnit();
            $tu->setSource($parsedEntry['msgid']);
            if ($parsedEntry['msgctxt'] !== '') {
                $tu->setContext($parsedEntry['msgctxt']);
            }
            if ($parsedEntry['msgid_plural'] !== '') {
                $tu->setSourcePlural($parsedEntry['msgid_plural']);
                $tu->setTargets($parsedEntry['msgstr_plural']);
            } elseif ($parsedEntry['msgstr'] !== '') {
                $tu->setTarget($parsedEntry['msgstr']);
            }

            $annotations = tao_helpers_translation_POUtils::unserializeAnnotations($parsedEntry['annotations']);
            foreach ($annotations as $name => $value) {
                $tu->addAnnotation($name, $value);
            }

            $tf->addTranslationUnit($tu);
        }

        $sourceLanguage = $tf->getHeaders()['sourceLanguage'] ?? '';
        $targetLanguage = $tf->getHeaders()['targetLanguage'] ?? '';

        /**
         * As a new process was introduced to translate languages on TAO (using Crowdin)
         * we can not rely on the targetLanguage attribute anymore as they will be always en-US
         * so the solution was to rely on the file's folder name
         */
        $matchedTargetLanguage = $targetLanguage;
        $targetLanguageFromFileName = basename(dirname($file));

        if (
            strstr($matchedTargetLanguage, DEFAULT_LANG)
            && $targetLanguageFromFileName !== ""
            && preg_match("/^[a-z]{2}-[A-Z]{2}$/", $targetLanguageFromFileName)
        ) {
            $matchedTargetLanguage = $targetLanguageFromFileName;
        }

        if ($sourceLanguage !== '') {
            $tf->setSourceLanguage(substr($sourceLanguage, 0, 5));
        }
        if ($matchedTargetLanguage !== '') {
            $tf->setTargetLanguage(substr($matchedTargetLanguage, 0, 5));
        }

        $this->setTranslationFile($tf);
    }

    /**
     * @param string $entry
     * @return array|null
     */
    private function parseEntry($entry)
    {
        $parsed = [
            'annotations' => '',
            'msgctxt' => '',
            'msgid' => '',
            'msgid_plural' => '',
            'msgstr' => '',
            'msgstr_plural' => [],
        ];

        $currentField = null;
        $currentPluralIndex = null;
        $annotationLines = [];

        foreach (preg_split("/\r?\n/", $entry) as $line) {
            if ($line === '') {
                continue;
            }

            if (preg_match('/^#/', $line)) {
                $annotationLines[] = $line;
                continue;
            }

            if (preg_match('/^msgctxt\s+"(.*)"$/', $line, $matches)) {
                $currentField = 'msgctxt';
                $currentPluralIndex = null;
                $parsed[$currentField] = $matches[1];
                continue;
            }

            if (preg_match('/^msgid_plural\s+"(.*)"$/', $line, $matches)) {
                $currentField = 'msgid_plural';
                $currentPluralIndex = null;
                $parsed[$currentField] = $matches[1];
                continue;
            }

            if (preg_match('/^msgid\s+"(.*)"$/', $line, $matches)) {
                $currentField = 'msgid';
                $currentPluralIndex = null;
                $parsed[$currentField] = $matches[1];
                continue;
            }

            if (preg_match('/^msgstr\[(\d+)\]\s+"(.*)"$/', $line, $matches)) {
                $currentField = 'msgstr_plural';
                $currentPluralIndex = (int) $matches[1];
                $parsed[$currentField][$currentPluralIndex] = $matches[2];
                continue;
            }

            if (preg_match('/^msgstr\s+"(.*)"$/', $line, $matches)) {
                $currentField = 'msgstr';
                $currentPluralIndex = null;
                $parsed[$currentField] = $matches[1];
                continue;
            }

            if (preg_match('/^"(.*)"$/', $line, $matches) && $currentField !== null) {
                if ($currentField === 'msgstr_plural') {
                    $parsed[$currentField][$currentPluralIndex] .= $matches[1];
                } else {
                    $parsed[$currentField] .= $matches[1];
                }
            }
        }

        if (
            $parsed['msgid'] === ''
            && $parsed['msgstr'] === ''
            && $parsed['msgid_plural'] === ''
            && empty($parsed['msgstr_plural'])
        ) {
            return null;
        }

        $parsed['annotations'] = implode("\n", $annotationLines);
        $parsed['msgctxt'] = tao_helpers_translation_POUtils::sanitize($parsed['msgctxt']);
        $parsed['msgid'] = tao_helpers_translation_POUtils::sanitize($parsed['msgid']);
        $parsed['msgid_plural'] = tao_helpers_translation_POUtils::sanitize($parsed['msgid_plural']);
        $parsed['msgstr'] = tao_helpers_translation_POUtils::sanitize($parsed['msgstr']);
        foreach ($parsed['msgstr_plural'] as $index => $value) {
            $parsed['msgstr_plural'][$index] = tao_helpers_translation_POUtils::sanitize($value);
        }

        return $parsed;
    }

    /**
     * @param string $rawHeaders
     * @return array
     */
    private function extractHeaders($rawHeaders)
    {
        $headers = [];
        foreach (explode("\n", tao_helpers_translation_POUtils::sanitize($rawHeaders)) as $headerLine) {
            if ($headerLine === '') {
                continue;
            }

            $separatorPosition = strpos($headerLine, ':');
            if ($separatorPosition === false) {
                continue;
            }

            $name = substr($headerLine, 0, $separatorPosition);
            $value = trim(substr($headerLine, $separatorPosition + 1));
            $headers[$name] = $value;
        }

        return $headers;
    }
}
