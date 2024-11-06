<?php

/*
 * East Common.
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

namespace Teknoo\East\Common\Recipe\Step\FrontAsset;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Common\FrontAsset\FileType;
use Teknoo\East\Common\FrontAsset\FinalFile;
use Teknoo\East\Common\Recipe\Step\Traits\ResponseTrait;
use Teknoo\East\Foundation\Client\ClientInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ReturnFile
{
    use ResponseTrait;

    public function __construct(
        private readonly StreamFactoryInterface $streamFactory,
        ResponseFactoryInterface $responseFactory,
    ) {
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(
        ClientInterface $client,
        FinalFile $file,
        FileType $type,
    ): self {
        $response = $this->responseFactory->createResponse(200);

        $headers = [
            'content-type' => match ($type) {
                FileType::JS => 'text/javascript; charset=utf-8',
                FileType::CSS => 'text/css; charset=utf-8',
                FileType::GIF => 'image/gif',
                FileType::JPEG => 'image/jpeg',
                FileType::PNG => 'image/png',
                FileType::SVG => 'image/svg+xml',
                FileType::WEBP => 'image/webp',
            }
        ];

        $response = $this->addHeadersIntoResponse($response, $headers);
        $stream = $this->streamFactory->createStream();
        $stream->write($file->getContent());

        $response = $response->withBody($stream);

        $client->acceptResponse($response);

        return $this;
    }
}
