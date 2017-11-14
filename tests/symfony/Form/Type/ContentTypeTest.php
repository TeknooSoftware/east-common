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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Website\Object\Block;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\WebsiteBundle\Form\Type\ContentType;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Form\Type\ContentType
 * @covers      \Teknoo\East\WebsiteBundle\Form\Type\TranslatableTrait
 */
class ContentTypeTest extends \PHPUnit\Framework\TestCase
{
    public function buildForm()
    {
        return new ContentType();
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::any())
            ->method('addEventListener')
            ->willReturnCallback(function ($name, $callable) {
                $form = $this->createMock(FormInterface::class);
                $content = new Content();
                $type = new Type();
                $type->setBlocks([
                    new Block('foo', 'text'),
                    new Block('foo2', 'textarea'),
                    new Block('foo3', 'numeric')
                ]);
                $content->setType($type);
                $content->setParts(['foo' => 'bar']);

                $event = new FormEvent($form, $content);
                $callable($event);
            });

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testBuildFormSubmittedData()
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::any())
            ->method('addEventListener')
            ->willReturnCallback(function ($name, $callable) {

                $form = $this->createMock(FormInterface::class);
                $content = new Content();
                $type = new Type();
                $type->setBlocks([new Block('foo', 'text'), new Block('foo2', 'text')]);
                $content->setType($type);
                $form->expects(self::any())->method('getNormData')->willReturn($content);

                $event = new FormEvent($form, ['foo'=>'bar', 'foo2'=>'bar']);
                $callable($event);
            });

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }
}
