<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Recipe\Step\FrontAsset;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Common\FrontAsset\FileType;
use Teknoo\East\Common\FrontAsset\FinalFile;
use Teknoo\East\Common\Recipe\Step\FrontAsset\ReturnFile;
use Teknoo\East\Foundation\Client\ClientInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ReturnFile::class)]
class ReturnFileTest extends TestCase
{
    public function testInvoke(): void
    {
        $responseFactory = $this->createStub(ResponseFactoryInterface::class);

        $response = $this->createStub(ResponseInterface::class);
        $responseFactory
            ->method('createResponse')
            ->willReturn($response);

        $response
            ->method('withHeader')
            ->willReturnSelf();

        $response
            ->method('withBody')
            ->willReturnSelf();

        $this->assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createStub(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createStub(ClientInterface::class),
                $this->createStub(FinalFile::class),
                FileType::JS,
            ),
        );

        $this->assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createStub(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createStub(ClientInterface::class),
                $this->createStub(FinalFile::class),
                FileType::CSS,
            ),
        );

        $this->assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createStub(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createStub(ClientInterface::class),
                $this->createStub(FinalFile::class),
                FileType::JPEG,
            ),
        );

        $this->assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createStub(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createStub(ClientInterface::class),
                $this->createStub(FinalFile::class),
                FileType::GIF,
            ),
        );

        $this->assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createStub(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createStub(ClientInterface::class),
                $this->createStub(FinalFile::class),
                FileType::PNG,
            ),
        );

        $this->assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createStub(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createStub(ClientInterface::class),
                $this->createStub(FinalFile::class),
                FileType::SVG,
            ),
        );

        $this->assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createStub(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createStub(ClientInterface::class),
                $this->createStub(FinalFile::class),
                FileType::WEBP,
            ),
        );
    }
}
