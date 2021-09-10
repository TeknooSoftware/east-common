<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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

namespace Teknoo\Tests\East\WebsiteBundle\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Teknoo\East\WebsiteBundle\Command\CreateUserCommand;
use Teknoo\East\Website\Writer\UserWriter;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Command\CreateUserCommand
 */
class CreateUserCommandTest extends TestCase
{
    /**
     * @var UserWriter
     */
    private $writer;

    /**
     * @var UserPasswordHasherInterface
     */
    private $userPasswordHasher;

    /**
     * @return UserWriter|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getWriter(): UserWriter
    {
        if (!$this->writer instanceof UserWriter) {
            $this->writer = $this->createMock(UserWriter::class);
        }

        return $this->writer;
    }

    /**
     * @return UserPasswordHasherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getUserPasswordHasher(): UserPasswordHasherInterface
    {
        if (!$this->userPasswordHasher instanceof UserPasswordHasherInterface) {
            $this->userPasswordHasher = new class implements UserPasswordHasherInterface {
                public function hashPassword(PasswordAuthenticatedUserInterface $user, string $plainPassword): string
                {
                    return 'fooBar';
                }
            };
        }

        return $this->userPasswordHasher;
    }

    public function buildCommand()
    {
        return new CreateUserCommand(
            $this->getWriter(),
            $this->getUserPasswordHasher()
        );
    }

    public function testExecution()
    {
        $input = $this->createMock(InputInterface::class);
        $input->expects(self::any())
            ->method('getArgument')
            ->willReturnMap([
                ['email', 'foo@bar'],
                ['first_name', 'foo'],
                ['last_name', 'bar'],
                ['password', 'foobar'],
            ]);

        $output = $this->createMock(OutputInterface::class);
        $output->expects(self::atLeastOnce())->method('writeln');

        $this->getWriter()
            ->expects(self::once())
            ->method('save')
            ->willReturnSelf();

        $this->buildCommand()->run(
            $input,
            $output
        );
    }
}
