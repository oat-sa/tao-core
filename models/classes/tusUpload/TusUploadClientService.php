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

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use League\Flysystem\File;
use oat\oatbox\Configurable;
use oat\tao\model\tusUpload\Clients\ClientAdapterInterface;
use oat\tao\model\tusUpload\exception\TusException;
use RuntimeException;

class TusUploadClientService extends AbstractTus implements TusUploadClientServiceInterface
{
    /** @const string  not common storage that can append file. */
    const OPTION_FILE_STORAGE = 'file_storage';

    /** @const string  Client Adapter to send requests (example: Guzzle */
    const OPTION_CLIENT = 'client';

    /** @const string  default chunk size to send */
    const OPTION_CHUNK_SIZE = 'chunk_size';

    /** @const string  server domain to send request (example www.test.com ) */
    const OPTION_SERVER_DOMAIN = 'server_domain';

    /** @const string  server relative url to send request (example /tus-server/entrypoint?key= ) */
    const OPTION_SERVER_RELATIVE_URL = 'server_relative_url';

    /** @const string  algorithm to calculate checksum for comparing */
    const CHECKSUM_ALGORITHM = 'sha256';

    /** @var ClientAdapterInterface */
    protected $client;

    /** @var string  URI that will be used to send requests. */
    protected $url;

    /** @var string  calculated file checksum */
    protected $checksum;

    /** @var string calculated file size */
    protected $fileSize;

    /** @var array additional information to send */
    protected $metadata = [];

    /**
     * @param File $file
     * @param string $fileName
     * @param string $key
     * @throws TusException
     */
    public function upload($filePath = '', $url = '', $key = '')
    {
        if(!empty($url)){
            $this->url = $url;
        }
        $this->addMetadata('filename', basename($filePath));
        $this->checksum = self::CHECKSUM_ALGORITHM . ' ' . base64_encode(hash_file(self::CHECKSUM_ALGORITHM, $filePath));
        $this->fileSize = filesize($filePath);
        try {
            // Check if this upload exists with HEAD request.
            $responseData = $this->sendHeadRequest($key);
            //assign key if it is new upload. Not tus protocol.
            if (empty($key)) {
                $key = $responseData['headers']['Upload-Key'];
            }
            $statusCode = $responseData['statusCode'];
            if (self::HTTP_OK !== $statusCode) {
                $this->create($key);
            }
            $offset = (int)$responseData['headers']['Upload-Offset'];
        } catch (ConnectException $e) {
            throw new TusException("Couldn't connect to server.");
        }

        $fs = $this->getTusFileStorageService();
        while ($fileChunk = $fs->readChunk($filePath, $offset, $this->getOption(self::OPTION_CHUNK_SIZE))) {
            // Continue upload with PATCH request.
            $this->sendPatchRequest($offset, $fileChunk, $key);
        }
    }

    /**
     * Add metadata. Additional data that will be send to server in 'Upload-Metadata` header
     *
     * @param string $key
     * @param string $value
     */
    public function addMetadata($key, $value)
    {
        $this->metadata[$key] = base64_encode($value);
    }

    /**
     * Get metadata.
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Get metadata for Upload-Metadata header.
     *
     * @return string
     */
    protected function getUploadMetadataHeader()
    {
        $metadata = [];
        foreach ($this->getMetadata() as $key => $value) {
            $metadata[] = "{$key} {$value}";
        }
        return implode(',', $metadata);
    }

    /**
     * Get assigned or created url
     *
     * @return string
     */
    public function getUrl()
    {
        if (!$this->url) {
            $this->url = $this->getOption(self::OPTION_SERVER_DOMAIN)
                . $this->getOption(self::OPTION_SERVER_RELATIVE_URL);
        }
        return $this->url;
    }

    /**
     * Create resource with POST request.
     *
     * @param string $key
     *
     * @throws RuntimeException
     *
     */
    protected function create($key)
    {
        $headers = [
            'Upload-Length'   => $this->fileSize,
            'Upload-Key'      => $key,
            'Upload-Checksum' => $this->checksum,
            'Upload-Metadata' => $this->getUploadMetadataHeader(),
        ];
        $responseData = $this->getClient()->post($this->getUrl() . $key, ['headers' => $headers]);
        if (self::HTTP_CREATED !== $responseData['statusCode']) {
            throw new RuntimeException('Unable to create resource.');
        }
    }

    /**
     * Send HEAD request.
     *
     * @return int
     * @throws RuntimeException
     *
     */
    protected function sendHeadRequest($key)
    {
        $responseData = $this->getClient()->head($this->getUrl() . $key, ['Upload-Key' => $key]);
        $statusCode = $responseData['status'];
        if (self::HTTP_OK !== $statusCode) {
            throw new RuntimeException('File not found.');
        }
        return (int)$responseData['headers']['upload-offset'];
    }

    /**
     * Send PATCH request.
     *
     * @param int $offset
     * @param string $fileChunk
     * @param string $key
     *
     * @return int
     * @throws RuntimeException
     * @throws TusException
     *
     */
    protected function sendPatchRequest($offset, $fileChunk, $key)
    {
        $headers = [
            'Content-Type'    => self::HEADER_CONTENT_TYPE,
            'Content-Length'  => strlen($fileChunk),
            'Upload-Checksum' => $this->checksum,
            'Upload-Offset'   => $offset
        ];
        try {
            $responseData = $this->getClient()->patch($this->getUrl() . $key, [
                'body'    => $fileChunk,
                'headers' => $headers,
            ]);
            return (int)$responseData['headers']['upload-offset'];
        } catch (ClientException $e) {
            throw $this->handleClientException($e);
        } catch (ConnectException $e) {
            throw new TusException("Couldn't connect to server.");
        }
    }

    /**
     * Handle client exception during patch request.
     *
     * @param ClientException $e
     *
     * @return TusException|RuntimeException
     */
    protected function handleClientException(ClientException $e)
    {
        $statusCode = $e->getResponse()->getStatusCode();
        if (self::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE === $statusCode) {
            $exception = new RuntimeException('The uploaded file is corrupt.');
        }
        if (self::HTTP_CONTINUE === $statusCode) {
            $exception = new TusException('Connection aborted by user.');
        }
        if (self::HTTP_UNSUPPORTED_MEDIA_TYPE === $statusCode) {
            $exception = new TusException('Unsupported media types.');
        }
        return isset($exception) ? $exception : new TusException($e->getResponse()->getBody(), $statusCode);
    }

    /**
     * @return ClientAdapterInterface
     */
    protected function getClient()
    {
        if (!$this->client) {
            $this->client = $this->getOption(self::OPTION_CLIENT);
            if ($this->client instanceof Configurable) {
                $this->client = $this->propagate($this->client);
            }
            $this->client->setDefaultHeaders(['Tus-Resumable' => self::TUS_PROTOCOL_VERSION]);
        }
        return $this->client;
    }
}
