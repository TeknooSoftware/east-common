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

namespace Teknoo\Tests\East\CommonBundle\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Teknoo\East\Common\Contracts\FrontAsset\MinifierInterface;
use Teknoo\East\Common\Contracts\FrontAsset\PersisterInterface;
use Teknoo\East\Common\Contracts\FrontAsset\SourceLoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\MinifierCommandInterface;
use Teknoo\East\CommonBundle\Command\CreateUserCommand;
use Teknoo\East\Common\Writer\UserWriter;
use Teknoo\East\CommonBundle\Command\MinifyCommand;
use Teknoo\East\Foundation\Command\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\FoundationBundle\Command\Client;
use Teknoo\East\Paas\Contracts\Recipe\Cookbook\RunJobInterface;
use Teknoo\East\Paas\Infrastructures\Symfony\Command\RunJobCommand;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers      \Teknoo\East\CommonBundle\Command\MinifyCommand
 */
class MinifyCommandTest extends TestCase
{
    /**
     * @var UserWriter
     */
    private $writer;

    private ?Executor $executor = null;

    private ?Client $client = null;

    private ?MinifierCommandInterface $minifierCommand = null;

    private ?MessageFactoryInterface $messageFactory = null;

    private ?SourceLoaderInterface $sourceLoader = null;

    private ?PersisterInterface $persister = null;

    private ?MinifierInterface $minifier = null;

    private function getExecutorMock(): Executor|MockObject
    {
        if (!$this->Executor instanceof Executor) {
            $this->Executor = $this->createMock(Executor::class);
        }

        return $this->Executor;
    }

    private function getClientMock(): Client|MockObject
    {
        if (!$this->client instanceof Client) {
            $this->client = $this->createMock(Client::class);
        }

        return $this->client;
    }

    private function getMinifierCommandMock(): MinifierCommandInterface|MockObject
    {
        if (!$this->minifierCommand instanceof MinifierCommandInterface) {
            $this->minifierCommand = $this->createMock(MinifierCommandInterface::class);
        }

        return $this->minifierCommand;
    }

    private function getMessageFactoryMock(): MessageFactoryInterface|MockObject
    {
        if (!$this->messageFactory instanceof MessageFactoryInterface) {
            $this->messageFactory = $this->createMock(MessageFactoryInterface::class);
        }

        return $this->messageFactory;
    }

    private function getSourceLoaderMock(): SourceLoaderInterface|MockObject
    {
        if (!$this->sourceLoader instanceof SourceLoaderInterface) {
            $this->sourceLoader = $this->createMock(SourceLoaderInterface::class);
        }

        return $this->sourceLoader;
    }

    private function getPersisterMock(): PersisterInterface|MockObject
    {
        if (!$this->persister instanceof PersisterInterface) {
            $this->persister = $this->createMock(PersisterInterface::class);
        }

        return $this->persister;
    }

    private function getMinifierMock(): MinifierInterface|MockObject
    {
        if (!$this->minifier instanceof MinifierInterface) {
            $this->minifier = $this->createMock(MinifierInterface::class);
        }

        return $this->minifier;
    }

    public function buildCommand(): MinifyCommand
    {
        return new MinifyCommand(
            'teknoo:common:minify:test',
            'Minify some file',
            $this->getExecutorMock(),
            $this->getClientMock(),
            $this->getMinifierCommandMock(),
            $this->getMessageFactoryMock(),
            $this->getSourceLoaderMock(),
            $this->getPersisterMock(),
            $this->getMinifierMock(),
            'css',
            '/foo',
        );
    }

    public function testExecutionFromInput()
    {
        $input = $this->createMock(InputInterface::class);
        $input->expects(self::any())
            ->method('getArgument')
            ->willReturn('fooBar');

        $request = $this->createMock(MessageInterface::class);
        $request->expects(self::any())
            ->method('withBody')
            ->willReturnSelf();

        $this->getMessageFactoryMock()
            ->expects(self::any())
            ->method('createMessage')
            ->willReturn($request);

        $output = $this->createMock(OutputInterface::class);

        self::assertEquals(
            0,
            $this->buildCommand()->run(
                $input,
                $output
            )
        );
    }
}
