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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Teknoo\East\CommonBundle\Command\CreateUserCommand;
use Teknoo\East\Common\Writer\UserWriter;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(CreateUserCommand::class)]
class CreateUserCommandTest extends TestCase
{
    private (UserWriter&MockObject)|null $writer = null;

    private ?UserPasswordHasherInterface $userPasswordHasher = null;

    public function getWriter(): UserWriter&MockObject
    {
        if (!$this->writer instanceof UserWriter) {
            $this->writer = $this->createMock(UserWriter::class);
        }

        return $this->writer;
    }

    public function getUserPasswordHasher(): UserPasswordHasherInterface
    {
        if (!$this->userPasswordHasher instanceof UserPasswordHasherInterface) {
            $this->userPasswordHasher = new class () implements UserPasswordHasherInterface {
                public function isPasswordValid(PasswordAuthenticatedUserInterface $user, string $plainPassword): bool
                {

                }

                public function needsRehash(PasswordAuthenticatedUserInterface $user): bool
                {

                }

                public function hashPassword(PasswordAuthenticatedUserInterface $user, string $plainPassword): string
                {
                    return 'fooBar';
                }
            };
        }

        return $this->userPasswordHasher;
    }

    public function buildCommand(): \Teknoo\East\CommonBundle\Command\CreateUserCommand
    {
        return new CreateUserCommand(
            $this->getWriter(),
            $this->getUserPasswordHasher()
        );
    }

    public function testExecution(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input
            ->method('getArgument')
            ->willReturnMap([
                ['email', 'foo@bar'],
                ['first_name', 'foo'],
                ['last_name', 'bar'],
                ['password', 'foobar'],
            ]);

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->atLeastOnce())->method('writeln');

        $this->getWriter()
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->buildCommand()->run(
            $input,
            $output
        );
    }

    public function testExecutionWithError(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input
            ->method('getArgument')
            ->willReturnMap([
                ['email', 'foo@bar'],
                ['first_name', 'foo'],
                ['last_name', 'bar'],
                ['password', 'foobar'],
            ]);

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->atLeastOnce())->method('writeln');

        $this->getWriter()
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception('foo'));

        $this->buildCommand()->run(
            $input,
            $output
        );
    }
}
