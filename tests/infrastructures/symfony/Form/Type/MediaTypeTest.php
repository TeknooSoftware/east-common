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

namespace Teknoo\Tests\East\CommonBundle\Form\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\CommonBundle\Form\Type\MediaType;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(MediaType::class)]
class MediaTypeTest extends TestCase
{
    public function buildForm()
    {
        return new MediaType();
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('addEventListener')
            ->willReturnCallback(function ($name, $callable) use ($builder) {
                self::assertEquals(FormEvents::PRE_SUBMIT, $name);

                $form = $this->createMock(FormInterface::class);
                $event = new FormEvent($form, []);
                $callable($event);

                return $builder;
            });

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testBuildFormPopulatedFileError()
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('addEventListener')
            ->willReturnCallback(function ($name, $callable) use ($builder) {
                self::assertEquals(FormEvents::PRE_SUBMIT, $name);

                $form = $this->createMock(FormInterface::class);
                $form->expects($this->once())->method('getNormData')
                    ->willReturn($this->createMock(Media::class));
                $image = $this->createMock(UploadedFile::class);
                $image->expects($this->any())->method('getError')->willReturn(1);
                $image->expects($this->never())->method('getSize')->willReturn(123);
                $form->expects($this->once())->method('addError');

                $event = new FormEvent($form, ['image' => $image]);
                $callable($event);

                return $builder;
            });

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testBuildFormPopulatedFile()
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('addEventListener')
            ->willReturnCallback(function ($name, $callable) use ($builder) {
                self::assertEquals(FormEvents::PRE_SUBMIT, $name);

                $form = $this->createMock(FormInterface::class);
                $form->expects($this->once())->method('getNormData')
                    ->willReturn($this->createMock(Media::class));
                $image = $this->createMock(UploadedFile::class);
                $image->expects($this->any())->method('getError')->willReturn(0);
                $image->expects($this->any())->method('getSize')->willReturn(123);
                $form->expects($this->never())->method('addError');
                $event = new FormEvent($form, ['image' => $image]);
                $callable($event);

                return $builder;
            });

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testConfigureOptions()
    {
        self::assertInstanceOf(
            MediaType::class,
            $this->buildForm()->configureOptions(
                $this->createMock(OptionsResolver::class)
            )
        );
    }
}
