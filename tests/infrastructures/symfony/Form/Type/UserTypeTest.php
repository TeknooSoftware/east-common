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

namespace Teknoo\Tests\East\CommonBundle\Form\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Form\Type\ApiUserType;
use Teknoo\East\CommonBundle\Form\Type\UserType;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiUserType::class)]
#[CoversClass(UserType::class)]
class UserTypeTest extends TestCase
{
    use FormTestTrait;

    public function buildForm(): UserType
    {
        return new UserType();
    }

    public function testBuildFormWithPopulatedUser(): void
    {
        $builder = $this->createStub(FormBuilderInterface::class);

        $builder
            ->method('add')
            ->willReturnSelf();

        $builder
            ->method('addEventListener')
            ->willReturnCallback(
                function (string $name, callable $callable) use ($builder): Stub {
                    $data = $this->createStub(User::class);
                    $data
                        ->method('getAuthData')
                        ->willReturn([
                            $this->createStub(StoredPassword::class)
                        ]);

                    $form = $this->createMock(FormInterface::class);
                    $form->method('get')->willReturnSelf();
                    $form->expects($this->once())->method('setData')->willReturnSelf();

                    $event = $this->createStub(FormEvent::class);
                    $event->method('getData')->willReturn($data);
                    $event->method('getForm')->willReturn($form);

                    $callable($event);

                    return $builder;
                }
            );

        $this->buildForm()->buildForm($builder, []);
        $this->assertTrue(true);
    }

    public function testBuildFormWithApi(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder
            ->method('add')
            ->willReturnSelf();

        $builder->expects($this->never())
            ->method('addEventListener');

        $this->buildForm()->buildForm($builder, ['api' => 'json']);
        $this->assertTrue(true);
    }

    public function testBuildFormWithNonPopulatedUser(): void
    {
        $builder = $this->createStub(FormBuilderInterface::class);

        $builder
            ->method('add')
            ->willReturnSelf();

        $builder
            ->method('addEventListener')
            ->willReturnCallback(
                function (string $name, callable $callable) use ($builder): Stub {
                    $data = $this->createStub(User::class);
                    $data
                        ->method('getAuthData')
                        ->willReturn([]);

                    $form = $this->createMock(FormInterface::class);
                    $form->method('get')->willReturnSelf();
                    $form->expects($this->once())->method('setData')->willReturnSelf();

                    $event = $this->createStub(FormEvent::class);
                    $event->method('getData')->willReturn($data);
                    $event->method('getForm')->willReturn($form);

                    $callable($event);

                    return $builder;
                }
            );

        $this->buildForm()->buildForm($builder, []);
        $this->assertTrue(true);
    }

    public function testBuildFormWithAuthenticatedThirdParyUser(): void
    {
        $builder = $this->createStub(FormBuilderInterface::class);

        $builder
            ->method('add')
            ->willReturnSelf();

        $builder
            ->method('addEventListener')
            ->willReturnCallback(
                function (string $name, callable $callable) use ($builder): Stub {
                    $data = $this->createStub(User::class);
                    $data
                        ->method('getAuthData')
                        ->willReturn([
                            $this->createStub(AuthDataInterface::class)
                        ]);

                    $form = $this->createMock(FormInterface::class);
                    $form->method('get')->willReturnSelf();
                    $form->expects($this->once())->method('setData')->willReturnSelf();

                    $event = $this->createStub(FormEvent::class);
                    $event->method('getData')->willReturn($data);
                    $event->method('getForm')->willReturn($form);

                    $callable($event);

                    return $builder;
                }
            );

        $this->buildForm()->buildForm($builder, []);
        $this->assertTrue(true);
    }

    public function testConfigureOptions(): void
    {
        $this->buildForm()->configureOptions(
            $this->createStub(OptionsResolver::class)
        );
        $this->assertTrue(true);
    }
}
