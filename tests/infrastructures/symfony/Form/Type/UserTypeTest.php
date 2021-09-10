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

namespace Teknoo\Tests\East\WebsiteBundle\Form\Type;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Website\Contracts\User\AuthDataInterface;
use Teknoo\East\Website\Object\StoredPassword;
use Teknoo\East\Website\Object\User;
use Teknoo\East\WebsiteBundle\Form\Type\UserType;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Form\Type\UserType
 */
class UserTypeTest extends TestCase
{
    use FormTestTrait;

    public function buildForm(): UserType
    {
        return new UserType;
    }

    public function testBuildFormWithPopulatedUser()
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::any())
            ->method('add')
            ->willReturnSelf();

        $builder->expects(self::any())
            ->method('addEventListener')
            ->willReturnCallback(
                function (string $name, callable $callable) use ($builder) {
                    $data = $this->createMock(User::class);
                    $data->expects(self::any())
                        ->method('getAuthData')
                        ->willReturn([
                            $this->createMock(StoredPassword::class)
                        ]);

                    $form = $this->createMock(FormInterface::class);
                    $form->expects(self::any())->method('get')->willReturnSelf();
                    $form->expects(self::once())->method('setData')->willReturnSelf();

                    $event = $this->createMock(FormEvent::class);
                    $event->expects(self::any())->method('getData')->willReturn($data);
                    $event->expects(self::any())->method('getForm')->willReturn($form);

                    $callable($event);

                    return $builder;
                }
            );

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testBuildFormWithNonPopulatedUser()
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::any())
            ->method('add')
            ->willReturnSelf();

        $builder->expects(self::any())
            ->method('addEventListener')
            ->willReturnCallback(
                function (string $name, callable $callable) use ($builder) {
                    $data = $this->createMock(User::class);
                    $data->expects(self::any())
                        ->method('getAuthData')
                        ->willReturn([]);

                    $form = $this->createMock(FormInterface::class);
                    $form->expects(self::any())->method('get')->willReturnSelf();
                    $form->expects(self::once())->method('setData')->willReturnSelf();

                    $event = $this->createMock(FormEvent::class);
                    $event->expects(self::any())->method('getData')->willReturn($data);
                    $event->expects(self::any())->method('getForm')->willReturn($form);

                    $callable($event);

                    return $builder;
                }
            );

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testBuildFormWithAuthenticatedThirdParyUser()
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::any())
            ->method('add')
            ->willReturnSelf();

        $builder->expects(self::any())
            ->method('addEventListener')
            ->willReturnCallback(
                function (string $name, callable $callable) use ($builder) {
                    $data = $this->createMock(User::class);
                    $data->expects(self::any())
                        ->method('getAuthData')
                        ->willReturn([
                            $this->createMock(AuthDataInterface::class)
                        ]);

                    $form = $this->createMock(FormInterface::class);
                    $form->expects(self::any())->method('get')->willReturnSelf();
                    $form->expects(self::once())->method('setData')->willReturnSelf();

                    $event = $this->createMock(FormEvent::class);
                    $event->expects(self::any())->method('getData')->willReturn($data);
                    $event->expects(self::any())->method('getForm')->willReturn($form);

                    $callable($event);

                    return $builder;
                }
            );

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }
}
