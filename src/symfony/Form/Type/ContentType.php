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

namespace Teknoo\East\WebsiteBundle\Form\Type;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Teknoo\East\Website\Object\Category;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Object\User;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ContentType extends AbstractType
{
    use TranslatableTrait;

    /**
     * To configure this form and fields to display.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('author', DocumentType::class, ['class' => User::class, 'required'=>true, 'multiple'=>false, 'choice_label'=>'username']);
        $builder->add('type', DocumentType::class, ['class' => Type::class, 'required'=>true, 'multiple'=>false, 'choice_label' => 'name']);
        $builder->add('categories', DocumentType::class, ['class' => Category::class, 'required'=>true, 'multiple'=>true, 'choice_label' => 'name']);
        $builder->add('title', TextType::class, ['required'=>true]);
        $builder->add('subtitle', TextType::class, ['required'=>false]);
        $builder->add('slug', TextType::class, ['required'=>false]);
        $builder->add('content', TextType::class, ['required'=>false]);
        $builder->add('description', TextType::class, ['required'=>false]);

        $this->addTranslatableLocaleFieldHidden($builder);
        $this->disableNonTranslatableField($builder, $options);
    }
}