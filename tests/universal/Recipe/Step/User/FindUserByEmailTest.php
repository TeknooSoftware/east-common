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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(FindUserByEmail::class)]
class FindUserByEmailTest extends TestCase
{
    public function buildStep(): FindUserByEmail
    {
        return new FindUserByEmail();
    }

    public function testInvokeBadLoader()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            new EmailValue(),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadId()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(LoaderInterface::class),
            new \stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadManager()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(LoaderInterface::class),
            new EmailValue(),
            new \stdClass()
        );
    }

    public function testInvokeFound()
    {
        $object = $this->createMock(UserInterface::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            UserInterface::class => $object
        ]);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($loader, $object) {
                    $promise->success($object);

                    return $loader;
                }
            );

        self::assertInstanceOf(
            FindUserByEmail::class,
            $this->buildStep()(
                $loader,
                new EmailValue('foo@bar'),
                $manager
            )
        );
    }

    public function testInvokeError()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('continue');
        $manager->expects($this->never())->method('updateWorkPlan');

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($loader) {
                    $promise->fail(new \DomainException('foo'));

                    return $loader;
                }
            );

        self::assertInstanceOf(
            FindUserByEmail::class,
            $this->buildStep()(
                $loader,
                new EmailValue('foo@bar'),
                $manager
            )
        );
    }
}
