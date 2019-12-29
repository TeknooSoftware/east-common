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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\AdminEndPoint;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Teknoo\East\WebsiteBundle\Command\CreateUserCommand;
use Teknoo\East\Website\Writer\UserWriter;
use Teknoo\East\WebsiteBundle\Object\User;

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
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

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
     * @return EncoderFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getEncoderFactory(): EncoderFactoryInterface
    {
        if (!$this->encoderFactory instanceof EncoderFactoryInterface) {
            $this->encoderFactory = $this->createMock(EncoderFactoryInterface::class);
        }

        return $this->encoderFactory;
    }

    public function buildCommand()
    {
        return new CreateUserCommand(
            $this->getWriter(),
            $this->getEncoderFactory()
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

        $encoder = $this->createMock(PasswordEncoderInterface::class);
        $encoder->expects(self::once())
            ->method('encodePassword')
            ->willReturn('fooBar');

        $this->getEncoderFactory()
            ->expects(self::once())
            ->method('getEncoder')
            ->with($this->callback(function ($instance) {
                return $instance instanceof User;
            }))
            ->willReturn($encoder);

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
