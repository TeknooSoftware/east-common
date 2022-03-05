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

namespace Teknoo\Tests\East\Website\Doctrine\Form\Type;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Website\Object\Block;
use Teknoo\East\Website\Doctrine\Object\Content;
use Teknoo\East\Website\Object\BlockType;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Doctrine\Form\Type\ContentType;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\Doctrine\Form\Type\ContentType
 * @covers      \Teknoo\East\Website\Doctrine\Form\Type\TranslatableTrait
 */
class ContentTypeTest extends TestCase
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
            ->willReturnCallback(function ($name, $callable) use ($builder) {
                $form = $this->createMock(FormInterface::class);
                $content = new Content();
                $type = new Type();
                $type->setBlocks([
                    new Block('foo', BlockType::Text),
                    new Block('foo2', BlockType::Textarea),
                    new Block('foo3', BlockType::Numeric),
                    new Block('foo3', BlockType::Image),
                    new Block('foo4', BlockType::Raw),
                ]);
                $content->setType($type);
                $content->setParts(['foo' => 'bar']);

                $event = new FormEvent($form, $content);
                $callable($event);

                return $builder;
            });

        $builder->expects(self::any())
            ->method('add')
            ->willReturnCallback(
                function ($child, $type, array $options = array()) use ($builder) {
                    if (DocumentType::class == $type && isset($options['query_builder'])) {
                        $qBuilder = $this->createMock(Builder::class);
                        $qBuilder->expects(self::once())
                            ->method('field')
                            ->with('deletedAt')
                            ->willReturnSelf();

                        $qBuilder->expects(self::once())
                            ->method('equals')
                            ->with(null)
                            ->willReturnSelf();

                        $repository = $this->createMock(DocumentRepository::class);
                        $repository->expects(self::once())
                            ->method('createQueryBuilder')
                            ->willReturn($qBuilder);

                        $options['query_builder']($repository);
                    }

                    return $builder;
                }
            );

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, ['doctrine_type' => ChoiceType::class])
        );
    }

    public function testBuildFormWithPublishedContent()
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::any())
            ->method('addEventListener')
            ->willReturnCallback(function ($name, $callable) use ($builder) {
                $form = $this->createMock(FormInterface::class);
                $content = new Content();
                $type = new Type();
                $type->setBlocks([
                    new Block('foo', BlockType::Text),
                    new Block('foo2', BlockType::Textarea),
                    new Block('foo3', BlockType::Image)
                ]);
                $content->setType($type);
                $content->setParts(['foo' => 'bar']);
                $content->setPublishedAt(new \DateTime('2017-11-01'));

                $event = new FormEvent($form, $content);
                $callable($event);

                return $builder;
            });

        $builder->expects(self::any())
            ->method('add')
            ->willReturnCallback(
                function ($child, $type, array $options = array()) use ($builder) {
                    if (DocumentType::class == $type && isset($options['query_builder'])) {
                        $qBuilder = $this->createMock(Builder::class);
                        $qBuilder->expects(self::once())
                            ->method('field')
                            ->with('deletedAt')
                            ->willReturnSelf();

                        $qBuilder->expects(self::once())
                            ->method('equals')
                            ->with(null)
                            ->willReturnSelf();

                        $repository = $this->createMock(DocumentRepository::class);
                        $repository->expects(self::once())
                            ->method('createQueryBuilder')
                            ->willReturn($qBuilder);

                        $options['query_builder']($repository);
                    }

                    return $builder;
                }
            );

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, ['doctrine_type' => ChoiceType::class])
        );
    }

    public function testBuildFormSubmittedData()
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::any())
            ->method('addEventListener')
            ->willReturnCallback(function ($name, $callable) use ($builder) {
                $form = $this->createMock(FormInterface::class);
                $content = new Content();
                $type = new Type();
                $type->setBlocks([new Block('foo', BlockType::Text), new Block('foo2', BlockType::Text)]);
                $content->setType($type);
                $form->expects(self::any())->method('getNormData')->willReturn($content);

                $event = new FormEvent($form, ['foo'=>'bar', 'foo2'=>'bar']);
                $callable($event);

                return $builder;
            });

        $builder->expects(self::any())
            ->method('add')
            ->willReturnCallback(
                function ($child, $type, array $options = array()) use ($builder) {
                    if (DocumentType::class == $type && isset($options['query_builder'])) {
                        $qBuilder = $this->createMock(Builder::class);
                        $qBuilder->expects(self::once())
                            ->method('field')
                            ->with('deletedAt')
                            ->willReturnSelf();

                        $qBuilder->expects(self::once())
                            ->method('equals')
                            ->with(null)
                            ->willReturnSelf();

                        $repository = $this->createMock(DocumentRepository::class);
                        $repository->expects(self::once())
                            ->method('createQueryBuilder')
                            ->willReturn($qBuilder);

                        $options['query_builder']($repository);
                    }

                    return $builder;
                }
            );

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, ['doctrine_type' => DocumentType::class])
        );
    }

    public function testConfigureOptions()
    {
        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->configureOptions(
                $this->createMock(OptionsResolver::class)
            )
        );
    }
}
