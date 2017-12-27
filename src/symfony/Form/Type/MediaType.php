<?php

declare(strict_types=1);

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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Teknoo\East\Website\Object\Media;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class MediaType extends AbstractType
{
    /**
     * To configure this form and fields to display.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     * @return self
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, ['required' => true]);
        $builder->add('alternative', TextType::class, ['required' => true]);
        $builder->add('image', FileType::class, ['required' => true, 'mapped' => false]);

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                $contentObject = $form->getNormData();

                if (!$contentObject instanceof Media || !isset($data['image'])
                    || !$data['image'] instanceof UploadedFile) {
                    return;
                }

                /**
                 * @var UploadedFile $image
                 */
                $image = $data['image'];
                $contentObject->setFile($image->getPathname());
                $contentObject->setLength($image->getSize());
                $contentObject->setMimeType((string) $image->getClientMimeType());
            }
        );

        return $this;
    }
}
