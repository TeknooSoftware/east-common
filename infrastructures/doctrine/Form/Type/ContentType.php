<?php

/*
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

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Form\Type;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Website\Doctrine\Object\Content;
use Teknoo\East\Website\Object\Content\Published;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Object\User;

/**
 * Symfony Form dedicated to manage translatable Content Object in a Symfony Website.
 * This form is placed in this namespace to use the good Symfony Form Doctrine Type to link a content to an author and
 * to a type. Author list and Type list are populated from theirs respective repository.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @SuppressWarnings(PHPMD)
 */
class ContentType extends AbstractType
{
    use TranslatableTrait;

    /**
     * @param FormBuilderInterface<Content> $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        $builder->add(
            'author',
            $options['doctrine_type'],
            [
                'class' => User::class,
                'required' => true,
                'multiple' => false,
                'choice_label' => 'userIdentifier',
                'query_builder' => static function (ObjectRepository $repository) {
                    return $repository->createQueryBuilder()
                        ->field('deletedAt')->equals(null);
                }
            ]
        );
        $builder->add(
            'type',
            $options['doctrine_type'],
            [
                'class' => Type::class,
                'required' => true,
                'multiple' => false,
                'choice_label' => 'name',
                'query_builder' => static function (ObjectRepository $repository) {
                    return $repository->createQueryBuilder()
                        ->field('deletedAt')->equals(null);
                }
            ]
        );
        $builder->add('title', TextType::class, ['required' => true]);
        $builder->add('subtitle', TextType::class, ['required' => false]);
        $builder->add('slug', TextType::class, ['required' => false]);
        $builder->add('description', TextareaType::class, ['required' => false]);
        $builder->add(
            'publishedAt',
            DateTimeType::class,
            [
                'required' => false,
                'attr' => ['readonly' => true],
                'widget' => 'single_text',
                'mapped' => false,
            ]
        );

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            static function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                if (!$data instanceof Content || !$data->getType() instanceof Type) {
                    return;
                }

                $data->isInState([Published::class], static function () use ($data, $form) {
                    $form->add(
                        'publishedAt',
                        DateTimeType::class,
                        [
                            'required' => false,
                            'attr' => ['readonly' => true],
                            'widget' => 'single_text',
                            'mapped' => false,
                            'data' => $data->getPublishedAt()
                        ]
                    );
                });

                $type = $data->getType();
                $parts = $data->getParts();

                foreach ($type->getBlocks() as $block) {
                    $formType = match ($block->getType()) {
                        'textarea' => TextareaType::class,
                        'raw' => TextareaType::class,
                        'numeric' => NumberType::class,
                        'text' => TextType::class,
                        'image' => TextType::class,
                        default => TextType::class,
                    };

                    $value = '';
                    if (isset($parts[$block->getName()])) {
                        $value = $parts[$block->getName()];
                    }

                    $form->add(
                        $block->getName(),
                        $formType,
                        [
                            'mapped' => false,
                            'data' => $value,
                            'required' => false,
                            'attr' => ['data-type' => $block->getType()]
                        ]
                    );
                }
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            static function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                $contentObject = $form->getNormData();

                if (!$contentObject instanceof Content || !$contentObject->getType() instanceof Type) {
                    return;
                }

                $type = $contentObject->getType();
                $contentValue = [];
                foreach ($type->getBlocks() as $block) {
                    if (isset($data[$block->getName()])) {
                        $contentValue[$block->getName()] = $data[$block->getName()];
                    }
                }

                $contentObject->setParts($contentValue);
            }
        );

        $this->addTranslatableLocaleFieldHidden($builder);

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'doctrine_type' => '',
        ));

        return $this;
    }
}
