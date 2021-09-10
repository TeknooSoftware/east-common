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

namespace Teknoo\East\WebsiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Teknoo\East\Website\Object\StoredPassword;
use Teknoo\East\Website\Object\User;
use Teknoo\East\WebsiteBundle\Object\PasswordAuthenticatedUser;

/**
 * Symfony form to edit East Website User.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface<PasswordAuthenticatedUser> $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        $builder->add('firstName', TextType::class, ['required' => true]);
        $builder->add('lastName', TextType::class, ['required' => true]);
        $builder->add(
            'roles',
            ChoiceType::class,
            [
                'required' => true,
                'multiple' => true,
                'choices' => [
                    'user' => 'ROLE_USER',
                    'admin' => 'ROLE_ADMIN'
                ]
            ]
        );

        $builder->add('email', EmailType::class, ['required' => true]);

        $builder->add('storedPassword', StoredPasswordType::class, ['mapped' => false]);

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            static function (FormEvent $event) {
                /**
                 * @var User $user
                 */
                $user = $event->getData();
                $spForm = $event->getForm()->get('storedPassword');

                foreach ($user->getAuthData() as $authData) {
                    if (!$authData instanceof StoredPassword) {
                        continue;
                    }

                    $spForm->setData($authData);
                    return;
                }

                $authData = new StoredPassword();
                $spForm->setData($authData);
                $user->addAuthData($authData);
            }
        );

        return $this;
    }
}
