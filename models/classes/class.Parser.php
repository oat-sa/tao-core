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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

declare(strict_types=1);

use oat\oatbox\filesystem\File;
use oat\tao\model\DomValidator;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\upload\UploadService;

/**
 * The Parser enables you to load, parse and validate xml content from an xml
 * Usually used for to load and validate the itemContent  property.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
class tao_models_classes_Parser
{
    public const SOURCE_FILE = 1;
    public const SOURCE_URL = 2;
    public const SOURCE_STRING = 3;
    public const SOURCE_FLYFILE = 4; // Current file is \oat\oatbox\filesystem\File object

    /**
     * XML content string
     *
     * @var string
     */
    protected $content = null;
    
    /** @var mixed */
    protected $source = '';

    /** @var int */
    protected $sourceType = 0;

    /** @var array */
    protected $errors = [];

    /** @var bool */
    protected $valid = false;

    /** @var string */
    protected $fileExtension = 'xml';

    /** @var DomValidator */
    private $domValidator;

    /**
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     */
    public function __construct($source, array $options = [])
    {
        $sourceType = false;

        if ($source instanceof File) {
            $sourceType = self::SOURCE_FLYFILE;
        } elseif (is_string($source)) {
            if (preg_match("/^<\?xml(.*)?/m", trim($source))) {
                $sourceType = self::SOURCE_STRING;
            } elseif (preg_match("/^http/", $source)) {
                $sourceType = self::SOURCE_URL;
            } elseif (is_file($source)) {
                $sourceType = self::SOURCE_FILE;
            } else {
                $uploadFile = ServiceManager::getServiceManager()->get(UploadService::SERVICE_ID)->universalizeUpload($source);
                if ($uploadFile instanceof File) {
                    $sourceType = self::SOURCE_FLYFILE;
                    $source = $uploadFile;
                }
            }
        }

        if ($sourceType === false) {
            throw new common_exception_Error(
                sprintf(
                    'Denied content in the source parameter! %s accepts either XML content, a URL to an XML Content '
                    . 'or the path to a file but got %s',
                    get_class($this),
                    substr($source, 0, 500)
                )
            );
        }

        $this->sourceType = $sourceType;
        $this->source = $source;

        if (isset($options['extension'])) {
            $this->fileExtension = $options['extension'];
        }
    }
    
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     *
     * @param string schema
     *
     * @return bool
     */
    public function validate($schema = '')
    {
        // You know sometimes you think you have enough time, but it is not always true ...
        // (timeout in hudson with the generis-hard test suite)
        helpers_TimeOutHelper::setTimeOutLimit(helpers_TimeOutHelper::MEDIUM);

        $content = $this->getContent();

        if (!empty($content)) {
            try {
                $this->valid = $this->getDomValidator()->validate($content, $schema);

                if (!$this->valid) {
                    $this->addErrors($this->getDomValidator()->getErrors());
                }
            } catch (DOMException $domException) {
                $this->addError($domException);
            }
        }

        helpers_TimeOutHelper::reset();

        return $this->valid;
    }

    /**
     * Execute parser validation and stops at the first valid one, and returns the identified schema
     */
    public function validateMultiple(array $xsds = []): string
    {
        $returnValue = '';

        foreach ($xsds as $xsd) {
            $this->errors = [];
            if ($this->validate($xsd)) {
                $returnValue = $xsd;
                break;
            }
        }

        return $returnValue;
    }
    
    /**
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     */
    public function displayErrors(bool $htmlOutput = true): string
    {
        $returnValue = '';

        foreach ($this->errors as $error) {
            $returnValue .= $error['message'];

            if (isset($error['file'], $error['line'])) {
                $returnValue .= sprintf(' in file %s, line %s', $error['file'], $error['line']);
            }

            $returnValue .= PHP_EOL;
        }

        if ($htmlOutput) {
            $returnValue = nl2br($returnValue);
        }

        return $returnValue;
    }
    
    /**
     * Get XML content.
     *
     * @author Aleh Hutnikau, <hutnikau@1pt.com>
     */
    public function getContent(bool $refresh = false): string
    {
        if ($this->content === null || $refresh) {
            try {
                switch ($this->sourceType) {
                    case self::SOURCE_FILE:
                        // Check file
                        if (!file_exists($this->source)) {
                            throw new Exception("File {$this->source} not found.");
                        }
                        if (!is_readable($this->source)) {
                            throw new Exception("Unable to read file {$this->source}.");
                        }
                        if (!preg_match("/\.{$this->fileExtension}$/", basename($this->source))) {
                            throw new Exception(
                                sprintf(
                                    'Wrong file extension in %s, %s extension is expected',
                                    basename($this->source),
                                    $this->fileExtension
                                )
                            );
                        }
                        if (!tao_helpers_File::securityCheck($this->source)) {
                            throw new Exception($this->source . ' seems to contain some security issues');
                        }

                        $this->content = file_get_contents($this->source);
                        break;
                    case self::SOURCE_URL:
                        // Only same domain
                        if (!preg_match("/^" . preg_quote(BASE_URL, '/') . "/", $this->source)) {
                            throw new Exception('The given uri must be in the domain ' . $_SERVER['HTTP_HOST']);
                        }

                        $this->content = tao_helpers_Request::load($this->source, true);
                        break;
                    case self::SOURCE_STRING:
                        $this->content = $this->source;
                        break;
                    case self::SOURCE_FLYFILE:
                        if (!$this->source->exists()) {
                            throw new Exception(
                                sprintf(
                                    'Source file does not exists ("%s").',
                                    $this->source->getBasename()
                                )
                            );
                        }
                        if (!$this->content = $this->source->read()) {
                            throw new Exception(
                                sprintf(
                                    'Unable to read file ("%s").',
                                    $this->source->getBasename()
                                )
                            );
                        }

                        break;
                }
            } catch (Exception $e) {
                $this->addError($e);
            }
        }
        
        return $this->content;
    }

    /**
     * Creates a report without title of the parsing result
     */
    public function getReport(): common_report_Report
    {
        if ($this->isValid()) {
            return common_report_Report::createSuccess('');
        } else {
            $report = new common_report_Report('');

            foreach ($this->getErrors() as $error) {
                $report->add(common_report_Report::createFailure($error['message']));
            }

            return $report;
        }
    }

    /**
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     */
    protected function addError($error)
    {
        $this->valid = false;

        if ($error instanceof Exception) {
            $this->errors[] = [
                'file' => $error->getFile(),
                'line' => $error->getLine(),
                'message' => sprintf('[%s] %s', get_class($error), $error->getMessage()),
            ];
        } elseif ($error instanceof LibXMLError) {
            $this->errors[] = [
                'file' => $error->file,
                'line' => $error->line,
                'message' => sprintf("[%s] %s", get_class($error), $error->message),
            ];
        } elseif (is_string($error)) {
            $this->errors[] = [
                'message' => $error,
            ];
        }
    }

    /**
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     */
    protected function addErrors(array $errors = []): void
    {
        foreach ($errors as $error) {
            $this->addError($error);
        }
    }

    /**
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     */
    protected function clearErrors(): void
    {
        $this->errors = [];
    }

    private function getDomValidator(): DomValidator
    {
        if (!isset($this->domValidator)) {
            $this->domValidator = ServiceManager::getServiceManager()->get(DomValidator::class);
        }

        return $this->domValidator;
    }
}
