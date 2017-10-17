<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Website\EndPoint;

use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Object\Media;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait MediaEndPointTrait
{
    /**
     * @var MediaLoader
     */
    private $mediaLoader;

    /**
     * MediaEndPointTrait constructor.
     * @param MediaLoader $mediaLoader
     */
    public function __construct(MediaLoader $mediaLoader)
    {
        $this->mediaLoader = $mediaLoader;
    }

    /**
     * @param ClientInterface $client
     * @param string $id
     * @return self
     */
    public function __invoke(ClientInterface $client, string $id)
    {
        $this->mediaLoader->byId(
           $id,
           new Promise(
               function (Media $medcia) use ($client) {
                   $client->responseFromController(
                       new Response(
                           new Stream($media->getResource()),
                           200,
                           [
                               'Content-Type' => $media->getMimeType(),
                               'Content-Length' => $media->getLength()
                           ]
                       )
                   );
               },
               function () use ($client) {
                   $client->errorInRequest(
                       new \Exception('Media is not available', 404)
                   );
               }
           )
        );

        return $this;
    }
}
