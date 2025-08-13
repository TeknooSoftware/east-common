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

namespace Teknoo\Tests\East\Common\Recipe\Step\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\User\UserInterface;
use Teknoo\East\Common\Object\EmailValue;
use Teknoo\East\Common\Recipe\Step\User\FindUserByEmail;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(FindUserByEmail::class)]
class FindUserByEmailTest extends TestCase
{
    public function buildStep(): FindUserByEmail
    {
        return new FindUserByEmail();
    }

    public function testInvokeBadLoader(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            new EmailValue(),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadId(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(LoaderInterface::class),
            new \stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadManager(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(LoaderInterface::class),
            new EmailValue(),
            new \stdClass()
        );
    }

    public function testInvokeFound(): void
    {
        $object = $this->createMock(UserInterface::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            UserInterface::class => $object
        ]);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->method('fetch')
            ->willReturnCallback(
                function (\Teknoo\East\Common\Contracts\Query\QueryElementInterface $query, PromiseInterface $promise) use ($loader, $object): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->success($object);

                    return $loader;
                }
            );

        $this->assertInstanceOf(
            FindUserByEmail::class,
            $this->buildStep()(
                $loader,
                new EmailValue('foo@bar'),
                $manager
            )
        );
    }

    public function testInvokeError(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('continue');
        $manager->expects($this->never())->method('updateWorkPlan');

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->method('fetch')
            ->willReturnCallback(
                function (\Teknoo\East\Common\Contracts\Query\QueryElementInterface $query, PromiseInterface $promise) use ($loader): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->fail(new \DomainException('foo'));

                    return $loader;
                }
            );

        $this->assertInstanceOf(
            FindUserByEmail::class,
            $this->buildStep()(
                $loader,
                new EmailValue('foo@bar'),
                $manager
            )
        );
    }
}
