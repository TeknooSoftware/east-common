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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ReturnFile::class)]
class ReturnFileTest extends TestCase
{
    public function testInvoke()
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $response = $this->createMock(ResponseInterface::class);
        $responseFactory->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

        $response->expects($this->any())
            ->method('withHeader')
            ->willReturnSelf();

        $response->expects($this->any())
            ->method('withBody')
            ->willReturnSelf();

        self::assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createMock(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createMock(ClientInterface::class),
                $this->createMock(FinalFile::class),
                FileType::JS,
            ),
        );

        self::assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createMock(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createMock(ClientInterface::class),
                $this->createMock(FinalFile::class),
                FileType::CSS,
            ),
        );

        self::assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createMock(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createMock(ClientInterface::class),
                $this->createMock(FinalFile::class),
                FileType::JPEG,
            ),
        );

        self::assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createMock(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createMock(ClientInterface::class),
                $this->createMock(FinalFile::class),
                FileType::GIF,
            ),
        );

        self::assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createMock(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createMock(ClientInterface::class),
                $this->createMock(FinalFile::class),
                FileType::PNG,
            ),
        );

        self::assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createMock(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createMock(ClientInterface::class),
                $this->createMock(FinalFile::class),
                FileType::SVG,
            ),
        );

        self::assertInstanceOf(
            ReturnFile::class,
            (new ReturnFile(
                $this->createMock(StreamFactoryInterface::class),
                $responseFactory,
            ))(
                $this->createMock(ClientInterface::class),
                $this->createMock(FinalFile::class),
                FileType::WEBP,
            ),
        );
    }
}
