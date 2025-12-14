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

namespace Teknoo\Tests\East\CommonBundle\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Teknoo\East\Common\Contracts\FrontAsset\MinifierInterface;
use Teknoo\East\Common\Contracts\FrontAsset\PersisterInterface;
use Teknoo\East\Common\Contracts\FrontAsset\SourceLoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\MinifierCommandInterface;
use Teknoo\East\CommonBundle\Command\CreateUserCommand;
use Teknoo\East\Common\Writer\UserWriter;
use Teknoo\East\CommonBundle\Command\MinifyCommand;
use Teknoo\East\Foundation\Command\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\FoundationBundle\Command\Client;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(MinifyCommand::class)]
class MinifyCommandTest extends TestCase
{
    private (Executor&Stub)|(Executor&MockObject)|null $executor = null;

    private (Client&Stub)|(Client&MockObject)|null $client = null;

    private (MinifierCommandInterface&Stub)|(MinifierCommandInterface&MockObject)|null $minifierCommand = null;

    private (MessageFactoryInterface&Stub)|(MessageFactoryInterface&MockObject)|null $messageFactory = null;

    private (SourceLoaderInterface&Stub)|(SourceLoaderInterface&MockObject)|null $sourceLoader = null;

    private (PersisterInterface&Stub)|(PersisterInterface&MockObject)|null $persister = null;

    private (MinifierInterface&Stub)|(MinifierInterface&MockObject)|null $minifier = null;

    private function getExecutorMock(bool $stub = false): (Executor&Stub)|(Executor&MockObject)
    {
        if (!$this->executor instanceof Executor) {
            if ($stub) {
                $this->executor = $this->createStub(Executor::class);
            } else {
                $this->executor = $this->createMock(Executor::class);
            }
        }

        return $this->executor;
    }

    private function getClientMock(bool $stub = false): (Client&Stub)|(Client&MockObject)
    {
        if (!$this->client instanceof Client) {
            if ($stub) {
                $this->client = $this->createStub(Client::class);
            } else {
                $this->client = $this->createMock(Client::class);
            }
        }

        return $this->client;
    }

    private function getMinifierCommandMock(bool $stub = false): (MinifierCommandInterface&Stub)|(MinifierCommandInterface&MockObject)
    {
        if (!$this->minifierCommand instanceof MinifierCommandInterface) {
            if ($stub) {
                $this->minifierCommand = $this->createStub(MinifierCommandInterface::class);
            } else {
                $this->minifierCommand = $this->createMock(MinifierCommandInterface::class);
            }
        }

        return $this->minifierCommand;
    }

    private function getMessageFactoryMock(bool $stub = false): (MessageFactoryInterface&Stub)|(MessageFactoryInterface&MockObject)
    {
        if (!$this->messageFactory instanceof MessageFactoryInterface) {
            if ($stub) {
                $this->messageFactory = $this->createStub(MessageFactoryInterface::class);
            } else {
                $this->messageFactory = $this->createMock(MessageFactoryInterface::class);
            }
        }

        return $this->messageFactory;
    }

    private function getSourceLoaderMock(bool $stub = false): (SourceLoaderInterface&Stub)|(SourceLoaderInterface&MockObject)
    {
        if (!$this->sourceLoader instanceof SourceLoaderInterface) {
            if ($stub) {
                $this->sourceLoader = $this->createStub(SourceLoaderInterface::class);
            } else {
                $this->sourceLoader = $this->createMock(SourceLoaderInterface::class);
            }
        }

        return $this->sourceLoader;
    }

    private function getPersisterMock(bool $stub = false): (PersisterInterface&Stub)|(PersisterInterface&MockObject)
    {
        if (!$this->persister instanceof PersisterInterface) {
            if ($stub) {
                $this->persister = $this->createStub(PersisterInterface::class);
            } else {
                $this->persister = $this->createMock(PersisterInterface::class);
            }
        }

        return $this->persister;
    }

    private function getMinifierMock(bool $stub = false): (MinifierInterface&Stub)|(MinifierInterface&MockObject)
    {
        if (!$this->minifier instanceof MinifierInterface) {
            if ($stub) {
                $this->minifier = $this->createStub(MinifierInterface::class);
            } else {
                $this->minifier = $this->createMock(MinifierInterface::class);
            }
        }

        return $this->minifier;
    }

    public function buildCommand(): MinifyCommand
    {
        return new MinifyCommand(
            'teknoo:common:minify:test',
            'Minify some file',
            $this->getExecutorMock(true),
            $this->getClientMock(true),
            $this->getMinifierCommandMock(true),
            $this->getMessageFactoryMock(true),
            $this->getSourceLoaderMock(true),
            $this->getPersisterMock(true),
            $this->getMinifierMock(true),
            'css',
            '/foo',
        );
    }

    public function testExecutionFromInput(): void
    {
        $input = $this->createStub(InputInterface::class);
        $input
            ->method('getArgument')
            ->willReturn('fooBar');

        $request = $this->createStub(MessageInterface::class);
        $request
            ->method('withBody')
            ->willReturnSelf();

        $this->getMessageFactoryMock(true)
            ->method('createMessage')
            ->willReturn($request);

        $output = $this->createStub(OutputInterface::class);

        $this->assertEquals(
            0,
            $this->buildCommand()->run(
                $input,
                $output
            )
        );
    }
}
