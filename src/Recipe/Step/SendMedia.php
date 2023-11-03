<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Recipe\Step;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Recipe\Step\Traits\ResponseTrait;
use Teknoo\East\Foundation\Client\ClientInterface;

/**
 * Recipe step to pass to the PSR11 response the stream corresponding to a loaded media in a previous step.
 * This step will also update reponses's headers to add content type and filename if they are defined in the media.
 * The lenght of the stream will be also put into headers.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SendMedia
{
    use ResponseTrait;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
    ) {
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(
        ClientInterface $client,
        Media $media,
        StreamInterface $stream
    ): self {
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

        return $this;
    }
}
