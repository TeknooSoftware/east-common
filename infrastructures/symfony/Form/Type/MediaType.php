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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\CommonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Object\MediaMetadata;

/**
 * Symfony form to edit East Website Media
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class MediaType extends AbstractType
{
    /**
     *@param FormBuilderInterface<Media> $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        $builder->add('name', TextType::class, ['required' => true]);
        $builder->add('alternative', TextType::class, ['required' => true, 'mapped' => false]);
        $builder->add('image', FileType::class, ['required' => true, 'mapped' => false]);

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            static function (FormEvent $event): void {
                $form = $event->getForm();
                /** @var array<string, string|UploadedFile> $data */
                $data = $event->getData();
                $mediaObject = $form->getNormData();

                if (
                    !$mediaObject instanceof Media
                    || !isset($data['image'])
                    || !$data['image'] instanceof UploadedFile
                ) {
                    return;
                }

                /**
                 * @var UploadedFile $image
                 */
                $image = $data['image'];

                if ($image->getError()) {
                    $form->addError(new FormError((string) $image->getErrorMessage()));
                    return;
                }

                $mediaObject->setLength((int) $image->getSize());
                $meta = new MediaMetadata(
                    (string) $image->getClientMimeType(),
                    (string) $image->getClientOriginalName(),
                    (string) ($data['alternative'] ?? ''),
                    (string) $image->getPathname()
                );
                $mediaObject->setMetadata($meta);
            }
        );

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);

        return $this;
    }
}
