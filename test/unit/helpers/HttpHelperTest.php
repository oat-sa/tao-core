<?php
/**
 * Created by PhpStorm.
 * User: siwane
 * Date: 20/03/19
 * Time: 11:29
 */

namespace oat\tao\test\unit\helpers;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use function GuzzleHttp\Psr7\stream_for;
use oat\generis\test\TestCase;
use oat\tao\model\http\ResponseEmitter;

class HttpHelperTest extends TestCase
{
    public function testGetStream()
    {
        $request = new ServerRequest('GET', '/', [
//            'Range' => '0-9'
        ]);
        $file = __DIR__ . '/../samples/zipBombCandidates/bomb-1GiB.zip';
        $response = \tao_helpers_Http::getStream(
            stream_for(fopen($file, 'r')),
            'application/zip',
            $request,
            new Response()
        );
        (new ResponseEmitter())($response);
    }
}