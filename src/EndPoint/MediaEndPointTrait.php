<?php

/*
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\EndPoint;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Object\Media;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait MediaEndPointTrait
{
    private MediaLoader $mediaLoader;

    protected StreamFactoryInterface $streamFactory;

    abstract protected function getStream(Media $media): StreamInterface;

    public function __construct(MediaLoader $mediaLoader, StreamFactoryInterface $streamFactory)
    {
        $this->mediaLoader = $mediaLoader;
        $this->streamFactory = $streamFactory;
    }

    public function __invoke(ClientInterface $client, string $id): self
    {
        $this->mediaLoader->load(
            $id,
            new Promise(
                function (Media $media) use ($client) {
                    try {
                        $stream = $this->getStream($media);
                    } catch (\Throwable $error) {
                        $client->errorInRequest($error);

                        return;
                    }

                    $response = $this->responseFactory->createResponse(200);
                    $metadata = $media->getMetadata();
                    if (null !== $metadata) {
                        $response = $response->withHeader('Content-Type', (string) $metadata->getContentType());
                        $response = $response->withHeader(
                            'Content-Disposition',
                            'attachment; filename="' . $metadata->getFileName() . '"'
                        );
                    }
                    $response = $response->withHeader('Content-Length', (string) $media->getLength());
                    $response = $response->withBody($stream);

                    $client->acceptResponse($response);
                },
                static function () use ($client) {
                    $client->errorInRequest(
                        new \Exception('Media is not available', 404)
                    );
                }
            )
        );

        return $this;
    }
}
