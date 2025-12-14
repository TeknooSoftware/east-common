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

namespace Teknoo\East\CommonBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Contracts\Form\FormApiAwareInterface;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;

/**
 * Symfony form to edit East Common User.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class UserType extends ApiUserType implements FormApiAwareInterface
{
    /**
     * @param FormBuilderInterface<PasswordAuthenticatedUser> $builder
     * @param array<string, mixed> $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        if (!empty($options['api'])) {
            return;
        }

        $builder->add('storedPassword', StoredPasswordType::class, ['mapped' => false]);

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            static function (FormEvent $event): void {
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
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => User::class,
            'api' => null,
        ]);

        $resolver->setAllowedTypes('api', ['null', 'string']);
    }
}
