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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\tusUpload;

use oat\oatbox\event\EventManager;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\tusUpload\Events\UploadCompleteEvent;
use oat\tao\model\tusUpload\Events\UploadCreatedEvent;
use oat\tao\model\tusUpload\Events\UploadProgressEvent;
use OutOfRangeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;
use RuntimeException;

class TusUploadServerService extends AbstractTus implements TusUploadServerServiceInterface
{
    /** @const string cache persistence to save temporary file meta data(not file) */
    const OPTION_CACHE_PERSISTENCE = 'cache';

    /** @const string upload max size */
    const OPTION_UPLOAD_MAX_SIZE = 'upload_max_size';

    /** @const string  not common storage that can append file. */
    const OPTION_FILE_STORAGE = 'file_storage';

    /** @const string Tus Creation Extension */
    const TUS_EXTENSION_CREATION = 'creation';

    /** @const string Tus Termination Extension */
    const TUS_EXTENSION_TERMINATION = 'termination';

    /** @const string Tus Checksum Extension */
    const TUS_EXTENSION_CHECKSUM = 'checksum';

    /** @const string Tus Expiration Extension */
    const TUS_EXTENSION_EXPIRATION = 'expiration';

    /** @const string Tus Concatenation Extension */
    const TUS_EXTENSION_CONCATENATION = 'concatenation';

    /** @const array All supported tus extensions */
    const TUS_EXTENSIONS = [
        self::TUS_EXTENSION_CREATION,
        self::TUS_EXTENSION_TERMINATION,
        self::TUS_EXTENSION_CHECKSUM,
        //self::TUS_EXTENSION_EXPIRATION,
        //self::TUS_EXTENSION_CONCATENATION,
    ];

    /** @const int 460 Checksum Mismatch */
    const HTTP_CHECKSUM_MISMATCH = 460;

    /** @const string Default checksum algorithm */
    const DEFAULT_CHECKSUM_ALGORITHM = 'sha256';

    /** @var ServerRequestInterface */
    protected $request;

    /** @var string */
    protected $uploadKey;

    protected $cachePersistence;
    /** @var TusFileStorageService */
    protected $tusFileStorageService;
    protected $eventManager;


    /**
     * @param ServerRequestInterface $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Get request.
     *
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get file checksum.
     *
     * @param string $filePath
     *
     * @return string
     */
    protected function getServerChecksum($filePath)
    {
        $checksumHeader = $this->getRequest()->header('Upload-Checksum');
        if (empty($checksumHeader)) {
            $checksumAlgorithm = self::DEFAULT_CHECKSUM_ALGORITHM;
        } else {
            list($checksumAlgorithm, /* $checksum */) = explode(' ', $checksumHeader);
        }
        return hash_file($checksumAlgorithm, $filePath);
    }

    /**
     * TODO: make separate function for  uiid
     * TODO: send back generated key
     * Get upload key from header.
     *
     * @return string|ResponseInterface
     */
    public function getUploadKey()
    {
        if (empty($this->uploadKey)) {
            $this->uploadKey = $this->getRequest()->getHeader('Upload-Key');
        }
        return !empty($this->uploadKey) ? $this->uploadKey : '';
    }

    /**
     * main entry point that will handle all requests.
     * @param ServerRequestInterface $request
     * @return array
     */
    public function serve(ServerRequestInterface $request)
    {
        $this->setRequest($request);
        $requestMethod = $this->getRequest()->getMethod();
        if (!in_array($requestMethod, $this->allowedHttpVerbs())) {
            return $this->prepareResponseData(null, self::HTTP_METHOD_NOT_ALLOWED);
        }
        if ($requestMethod != self::METHOD_HEAD && !$this->getUploadKey()) {
            return $this->prepareResponseData(null, self::HTTP_BAD_REQUEST);
        }
        $clientVersion = $this->getRequest()->getHeader('Tus-Resumable');

        if ($clientVersion && $clientVersion !== self::TUS_PROTOCOL_VERSION) {
            return $this->prepareResponseData(null, self::HTTP_PRECONDITION_FAILED, [
                'Tus-Version' => self::TUS_PROTOCOL_VERSION,
            ]);
        }
        $method = 'handle' . ucfirst(strtolower($requestMethod));
        return $this->{$method}();
    }

    /**
     * Handle OPTIONS request.
     *
     * @return ResponseInterface
     */
    protected function handleOptions()
    {
        $headers = [
            'Allow'                  => implode(',', $this->allowedHttpVerbs()),
            'Tus-Version'            => self::TUS_PROTOCOL_VERSION,
            'Tus-Extension'          => implode(',', self::TUS_EXTENSIONS),
            'Tus-Checksum-Algorithm' => $this->getSupportedHashAlgorithms(),
        ];

        $maxUploadSize = $this->getOption(self::OPTION_UPLOAD_MAX_SIZE);

        if ($maxUploadSize > 0) {
            $headers['Tus-Max-Size'] = $maxUploadSize;
        }

        return $this->prepareResponseData(null, self::HTTP_OK, $headers);
    }

    /**
     * Handle HEAD request.
     *
     * @return ResponseInterface
     */
    protected function handleHead()
    {
        $headers = [];
        if (!$fileMeta = json_decode($this->getCachePersistence()->get($this->getUploadKey()), true)) {
            $status = self::HTTP_NOT_FOUND;
        } elseif (empty($fileMeta['offset'])) {
            $status = self::HTTP_GONE;
        } else {
            $status = self::HTTP_OK;
            $headers = [
                'Upload-Length' => (int)$fileMeta['size'],
                'Upload-Offset' => (int)$fileMeta['offset'],
                'Cache-Control' => 'no-store',
            ];
            if (self::UPLOAD_TYPE_FINAL === $fileMeta['upload_type'] && $fileMeta['size'] !== $fileMeta['offset']) {
                unset($headers['Upload-Offset']);
            }
            if (self::UPLOAD_TYPE_NORMAL !== $fileMeta['upload_type']) {
                $headers += ['Upload-Concat' => $fileMeta['upload_type']];
            }
        }
        return $this->prepareResponseData(null, $status, $headers);
    }


    /**
     * Post request initiates uploading process
     * @return ResponseInterface
     * @throws \common_Exception
     */
    protected function handlePost()
    {
        $fileName = $this->extractMeta('name') ?: $this->extractMeta('filename');
        if (empty($fileName)) {
            return $this->prepareResponseData(null, self::HTTP_BAD_REQUEST);
        }
        $maxUploadSize = $this->getOption(self::OPTION_UPLOAD_MAX_SIZE);
        if (!($maxUploadSize > 0 && $this->getRequest()->getHeader('Upload-Length') > $maxUploadSize)) {
            return $this->prepareResponseData(null, self::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }
        $data = [
            'name'        => $fileName,
            'offset'      => 0,
            'size'        => $this->getRequest()->getHeader('Upload-Length'),
            'key'         => $this->getUploadKey(),
            'checksum'    => $this->getClientChecksum(),
            'upload_type' => self::UPLOAD_TYPE_NORMAL
        ];
        $this->setCacheData($this->getUploadKey(), $data);
        $this->getEventManager()->trigger(new UploadCreatedEvent($data, $this->getRequest()));
        return $this->prepareResponseData(null, self::HTTP_CREATED, ['Location' => $this->getUploadKey()]);
    }


    /**
     * Patch request to continue uploading
     * @return ResponseInterface
     * Handle PATCH request.
     *
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    protected function handlePatch()
    {
        $uploadComplete = false;
        if (!$meta = json_decode($this->getCachePersistence()->get($this->getUploadKey()), true)) {
            return $this->prepareResponseData(null, self::HTTP_GONE);
        }
        $responseData = [];
        $status = $this->verifyPatchRequest($meta);

        if (self::HTTP_OK !== $status) {
            return $this->prepareResponseData(null, $status);
        }
        try {
            $fs = $this->getTusFileStorageService();
            $offset = $fs->writeChunk($meta);
            // If upload is done, verify checksum.
            if ($offset === $meta['fileSize']) {
                if ($meta['checksum'] !== $this->getServerChecksum($meta['file_path'])) {
                    $responseData = $this->prepareResponseData(null, self::HTTP_CHECKSUM_MISMATCH);
                } else {
                    $this->getEventManager()->trigger(new UploadCompleteEvent($meta, $this->getRequest()));
                    $uploadComplete = true;
                }
            } else {
                $this->getEventManager()->trigger(new UploadProgressEvent($meta, $this->getRequest()));
            }
        } catch (RuntimeException $e) {
            $responseData = $this->prepareResponseData($e->getMessage(), self::HTTP_UNPROCESSABLE_ENTITY);
        } catch (OutOfRangeException $e) {
            $responseData = $this->prepareResponseData(null, self::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE);
        } catch (Exception $e) {
            $responseData = $this->prepareResponseData(null, self::HTTP_CONTINUE);
        }
        $this->setCacheData($meta['key'], ['offset' => $fs->getOffset()]);

        if (!$responseData) {
            $responseData = $this->prepareResponseData(null, self::HTTP_NO_CONTENT, [
                'Content-Type'   => self::HEADER_CONTENT_TYPE,
                'Upload-Expires' => $meta['expires_at'],
                'Upload-Offset'  => $offset,
            ], $uploadComplete);
        }
        return $responseData;
    }

    /**
     * Verify PATCH request.
     *
     * @param array $meta
     *
     * @return int
     */
    protected function verifyPatchRequest($meta)
    {
        $status = self::HTTP_OK;
        if (self::UPLOAD_TYPE_FINAL === $meta['upload_type']) {
            $status = self::HTTP_FORBIDDEN;
        }
        $uploadOffset = $this->getRequest()->getHeader('upload-offset');
        if ($uploadOffset && $uploadOffset !== (string)$meta['offset']) {
            $status = self::HTTP_CONFLICT;
        }
        $contentType = $this->getRequest()->getHeader('Content-Type');
        if ($contentType !== self::HEADER_CONTENT_TYPE) {
            $status = self::HTTP_UNSUPPORTED_MEDIA_TYPE;
        }
        return $status;
    }

    /**
     * Get list of supported hash algorithms.
     *
     * @return string
     */
    protected function getSupportedHashAlgorithms()
    {
        $supportedAlgorithms = hash_algos();

        $algorithms = [];
        foreach ($supportedAlgorithms as $hashAlgo) {
            if (false !== strpos($hashAlgo, ',')) {
                $algorithms[] = "'{$hashAlgo}'";
            } else {
                $algorithms[] = $hashAlgo;
            }
        }

        return implode(',', $algorithms);
    }

    /**
     * Verify and get upload checksum from header.
     *
     * @return string|ResponseInterface
     */
    protected function getClientChecksum()
    {
        $checksumHeader = $this->getRequest()->getHeader('Upload-Checksum');
        if (!empty($checksumHeader)) {
            list($checksumAlgorithm, $checksum) = explode(' ', $checksumHeader);
            $checksum = base64_decode($checksum);
            if (!in_array($checksumAlgorithm, hash_algos()) || false === $checksum) {
                return $this->prepareResponseData(null, self::HTTP_BAD_REQUEST);
            }
        }
        return $checksum ?: '';
    }

    /**
     * No other methods are allowed.
     *
     * @param string $method
     * @param array $params
     *
     * @return ResponseInterface
     */
    public function __call($method, $params)
    {
        return $this->prepareResponseData(null, self::HTTP_BAD_REQUEST);
    }


    /**
     * @return ConfigurableService
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function getEventManager()
    {
        if ($this->eventManager == null) {
            $this->eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        }
        return $this->eventManager;
    }

    /**
     * Supported http requests.
     *
     * @return array
     */
    public function allowedHttpVerbs()
    {
        return [
            self::METHOD_GET,
            self::METHOD_POST,
            self::METHOD_PATCH,
            self::METHOD_DELETE,
            self::METHOD_HEAD,
            self::METHOD_OPTIONS,
        ];
    }

    /**
     * @param $content
     * @param int $status
     * @param array $headers
     * @param bool $uploadComplete
     * @return array
     */
    protected function prepareResponseData($content, $status = self::HTTP_OK, $headers = [], $uploadComplete = false)
    {
        return ['content' => $content, 'status' => $status, 'headers' => $headers, 'uploadComplete' => $uploadComplete];
    }

    /**
     * Extracts the meta data from the request header.
     *
     * @param string $requestedKey
     *
     * @return string
     */
    protected function extractMeta($requestedKey)
    {
        $value = '';
        $uploadMetaData = $this->request->getHeader('Upload-Metadata');
        if (!empty($uploadMetaData)) {
            $uploadMetaDataChunks = explode(',', $uploadMetaData);
            foreach ($uploadMetaDataChunks as $chunk) {
                list($key, $value) = explode(' ', $chunk);
                if ($key === $requestedKey) {
                    $value = base64_decode($value);
                    break;
                }
            }
        }
        return $value;
    }

    /**
     * @return \common_persistence_KeyValuePersistence
     */
    protected function getCachePersistence()
    {
        if ($this->cachePersistence == null) {
            $persistenceId = $this->getOption(static::OPTION_CACHE_PERSISTENCE);
            $this->cachePersistence = $this->getServiceManager()->get(\common_persistence_Manager::SERVICE_ID)->getPersistenceById($persistenceId);
        }
        return $this->cachePersistence;
    }

    /**
     * @param $key
     * @param $data
     * @throws \common_Exception
     */
    protected function setCacheData($key, $data)
    {
        $previous = json_decode($this->getCachePersistence()->get($key), true);
        if ($previous) {
            $data = array_merge($previous, $data);
        }
        $this->getCachePersistence()->set($key, json_encode($data));
    }
}
