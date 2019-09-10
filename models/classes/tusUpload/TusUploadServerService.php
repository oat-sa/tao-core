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
use oat\tao\model\tusUpload\exception\ConnectionException;
use oat\tao\model\tusUpload\exception\FileException;
use oat\tao\model\tusUpload\exception\OutOfRangeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TusUploadServerService extends AbstractTus implements TusUploadServerServiceInterface
{
    /** @const string cache persistance to save temporary file meta data(not file) */
    const OPTION_CACHE_PERSISTANCE = 'cache';

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
        self::TUS_EXTENSION_EXPIRATION,
        self::TUS_EXTENSION_CONCATENATION,
    ];

    /** @const int 460 Checksum Mismatch */
    const HTTP_CHECKSUM_MISMATCH = 460;

    /** @const string Default checksum algorithm */
    const DEFAULT_CHECKSUM_ALGORITHM = 'sha256';

    /** @var ServerRequestInterface */
    protected $request;

    /** @var string */
    protected $uploadKey;

    protected $cachePersistance;
    /** @var TusFileStorageService */
    protected $tusFileStorageService;
    protected $eventManager;

    /**
     * @var int Max upload size in bytes
     *          Default 0, no restriction.
     */
    protected $maxUploadSize = 0;

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
    public function getServerChecksum(string $filePath)
    {
        return hash_file($this->getChecksumAlgorithm(), $filePath);
    }

    /**
     * Get checksum algorithm.
     *
     * @return string|null
     */
    public function getChecksumAlgorithm()
    {
        $checksumHeader = $this->getRequest()->header('Upload-Checksum');
        if (empty($checksumHeader)) {
            return self::DEFAULT_CHECKSUM_ALGORITHM;
        }
        list($checksumAlgorithm, /* $checksum */) = explode(' ', $checksumHeader);
        return $checksumAlgorithm;
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
        if (!empty($this->uploadKey)) {
            return $this->uploadKey;
        }
        $key = $this->getRequest()->getHeader('Upload-Key') ?? mt_rand(234);
        if (empty($key)) {
            return $this->prepareResponseData(null, TusResponse::HTTP_BAD_REQUEST);
        }
        $this->uploadKey = $key;
        return $this->uploadKey;
    }

    /**
     * Set max upload size.
     *
     * @param int $uploadSize
     *
     * @return TusUploadServerService
     */
    public function setMaxUploadSize(int $uploadSize)
    {
        $this->maxUploadSize = $uploadSize;

        return $this;
    }

    /**
     * Get max upload size.
     *
     * @return int
     */
    public function getMaxUploadSize()
    {
        return $this->maxUploadSize;
    }

    /**
     * main entry point that will handle all requests.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function serve(ServerRequestInterface $request)
    {
        $this->setRequest($request);
        $requestMethod = $this->getRequest()->getMethod();
        if (!in_array($requestMethod, $this->allowedHttpVerbs())) {
            return $this->prepareResponseData(null, TusResponse::HTTP_METHOD_NOT_ALLOWED);
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

        $maxUploadSize = $this->getMaxUploadSize();

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
        if (!$fileMeta = json_decode($this->getCachePersistance()->get($this->getUploadKey()), true)) {
            $status = self::HTTP_NOT_FOUND;
        } elseif (empty($fileMeta['offset'])) {
            $status = self::HTTP_GONE;
        } else {
            $status = self::HTTP_OK;
            $headers = $this->getHeadersForHeadRequest($fileMeta);
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
        $uploadType = self::UPLOAD_TYPE_NORMAL;
        if (empty($fileName)) {
            return $this->prepareResponseData(null, self::HTTP_BAD_REQUEST);
        }
        if (!$this->verifyUploadSize()) {
            return $this->prepareResponseData(null, self::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }
        $uploadKey = $this->getUploadKey();
        if (self::UPLOAD_TYPE_PARTIAL === $this->getRequest()->getHeader('Upload-Concat')) {
            $uploadType = self::UPLOAD_TYPE_PARTIAL;
        }
        $checksum = $this->getClientChecksum();
        $data = [
            'name'        => $fileName,
            'offset'      => 0,
            'size'        => $this->getRequest()->getHeader('Upload-Length'),
            //'file_path'   => $filePath,
            'key'         => $uploadKey,
            'checksum'    => $checksum,
            'upload_type' => $uploadType
        ];
        $this->getCachePersistance()->set($uploadKey, json_encode($data));
        $this->getEventManager()->trigger(new UploadCreatedEvent($data, $this->getRequest()));

        return $this->prepareResponseData(
            null,
            self::HTTP_CREATED,
            [
                'Location'       => $this->getUploadKey(), //todo: rewrite to key\path. location used in original library.
                'Upload-Expires' => $data['expires_at'],
            ]
        );
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
        $uploadKey = $this->getUploadKey();
        if (!$meta = json_decode($this->getCachePersistance()->get($uploadKey), true)) {
            return $this->prepareResponseData(null, self::HTTP_GONE);
        }
        $status = $this->verifyPatchRequest($meta);

        if (self::HTTP_OK !== $status) {
            return $this->prepareResponseData(null, $status);
        }
        try {
            //todo: recheck offset usage in original library
            $fs = $this->getTusFileStorageService();
            $offset = $fs->upload($meta);
            // If upload is done, verify checksum.
            if ($offset === $meta['fileSize']) {
                if ($meta['checksum'] !== $this->getServerChecksum($meta['file_path'])) {
                    return $this->prepareResponseData(null, self::HTTP_CHECKSUM_MISMATCH);
                }
                $this->getEventManager()->trigger(new UploadCompleteEvent($meta, $this->getRequest()));
            } else {
                $this->getEventManager()->trigger(new UploadProgressEvent($meta, $this->getRequest()));
            }
        } catch (FileException $e) {
            return $this->prepareResponseData($e->getMessage(), self::HTTP_UNPROCESSABLE_ENTITY);
        } catch (OutOfRangeException $e) {
            return $this->prepareResponseData(null, self::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE);
        } catch (ConnectionException $e) {
            return $this->prepareResponseData(null, self::HTTP_CONTINUE);
        }

        return $this->prepareResponseData(null, self::HTTP_NO_CONTENT, [
            'Content-Type'   => self::HEADER_CONTENT_TYPE,
            'Upload-Expires' => $meta['expires_at'],
            'Upload-Offset'  => $offset,
        ]);
    }

    /**
     * Verify PATCH request.
     *
     * @param array $meta
     *
     * @return int
     */
    protected function verifyPatchRequest(array $meta)
    {
        if (self::UPLOAD_TYPE_FINAL === $meta['upload_type']) {
            return self::HTTP_FORBIDDEN;
        }
        $uploadOffset = $this->getRequest()->getHeader('upload-offset');
        if ($uploadOffset && $uploadOffset !== (string)$meta['offset']) {
            return self::HTTP_CONFLICT;
        }
        $contentType = $this->getRequest()->getHeader('Content-Type');
        if ($contentType !== self::HEADER_CONTENT_TYPE) {
            return self::HTTP_UNSUPPORTED_MEDIA_TYPE;
        }
        return self::HTTP_OK;
    }


    /**
     * @return ResponseInterface
     *
     * Handle DELETE request.
     *
     * @todo : rewrite unlink
     */
    protected function handleDelete()
    {
        $key = $this->getUploadKey();
        $fileMeta = $this->getCachePersistance()->get($key);
        $resource = $fileMeta['file_path'] ?? null;

        if (!$resource) {
            return $this->prepareResponseData(null, self::HTTP_NOT_FOUND);
        }

        $isDeleted = $this->getCachePersistance()->delete($key);// todo: find how delete on KV_Storage works

        if (!$isDeleted || !file_exists($resource)) {
            return $this->prepareResponseData(null, self::HTTP_GONE);
        }

        unlink($resource);
        return $this->prepareResponseData(null, self::HTTP_NO_CONTENT, [
            'Tus-Extension' => self::TUS_EXTENSION_TERMINATION,
        ]);
    }

    /**
     * Get required headers for head request.
     *
     * @param array $fileMeta
     *
     * @return array
     */
    protected function getHeadersForHeadRequest(array $fileMeta)
    {
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

        return $headers;
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

        if (empty($checksumHeader)) {
            return '';
        }

        list($checksumAlgorithm, $checksum) = explode(' ', $checksumHeader);

        $checksum = base64_decode($checksum);

        if (!in_array($checksumAlgorithm, hash_algos()) || false === $checksum) {
            return $this->prepareResponseData(null, self::HTTP_BAD_REQUEST);
        }

        return $checksum;
    }

    /**
     * Verify max upload size.
     *
     * @return bool
     */
    protected function verifyUploadSize()
    {
        $maxUploadSize = $this->getMaxUploadSize();
        return !($maxUploadSize > 0 && $this->getRequest()->getHeader('Upload-Length') > $maxUploadSize);
    }

    /**
     * No other methods are allowed.
     *
     * @param string $method
     * @param array $params
     *
     * @return ResponseInterface
     */
    public function __call(string $method, array $params)
    {
        return $this->prepareResponseData(null, self::HTTP_BAD_REQUEST);
    }

    /**
     * @return \common_persistence_KeyValuePersistence
     */
    public function getCachePersistance()
    {
        if ($this->cachePersistance == null) {
            $persistenceId = $this->getOption(self::OPTION_CACHE_PERSISTANCE);
            $this->cachePersistance = $this->getServiceManager()->get(\common_persistence_Manager::SERVICE_ID)->getPersistenceById($persistenceId);
        }
        return $this->cachePersistance;
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
     * Getting path to the folder with Generated packages for synchronization
     * @return TusFileStorageService
     */
    private function getTusFileStorageService()
    {
        if (!$this->tusFileStorageService) {
            $this->tusFileStorageService = $this->propagate($this->getOption(self::OPTION_FILE_STORAGE));
            $this->tusFileStorageService->setCachePersistence($this->getCachePersistance());
        }
        return $this->tusFileStorageService;
    }

    protected function prepareResponseData($content, int $status = self::HTTP_OK, array $headers = [])
    {
        return ['content' => $content, 'status' => $status, 'headers' => $headers];
    }

    /**
     * Extracts the meta data from the request header.
     *
     * @param string $requestedKey
     *
     * @return string
     */
    public function extractMeta(string $requestedKey)
    {
        $uploadMetaData = $this->request->getHeader('Upload-Metadata');
        if (empty($uploadMetaData)) {
            return '';
        }
        $uploadMetaDataChunks = explode(',', $uploadMetaData);
        foreach ($uploadMetaDataChunks as $chunk) {
            list($key, $value) = explode(' ', $chunk);
            if ($key === $requestedKey) {
                return base64_decode($value);
            }
        }
        return '';
    }

}