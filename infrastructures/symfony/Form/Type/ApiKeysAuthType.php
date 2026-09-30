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

use DateTimeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContext;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\CommonBundle\Object\ApiKeysAuthUser;
use Teknoo\East\Foundation\Time\DatesService;

use function bin2hex;
use function random_bytes;

/**
 * Symfony form to create a new API key (an ApiKeyToken instance) for the current authenticated user : the form asks
 * the name of the key and its expiration date. When the form is valid, the token of the key is generated (with an
 * optional prefix), hashed like a password and the key is added to the ApiKeysAuth instance of the user.
 * The token is available, into the ApiKeyToken instance, only during this request : it is not persisted.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiKeysAuthType extends AbstractType
{
    public function __construct(
        private readonly DatesService $datesService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly string $tokenPrefix = '',
    ) {
    }

    private function findApiKeysAuth(User $user): ?ApiKeysAuth
    {
        foreach ($user->getAuthData() as $authData) {
            if ($authData instanceof ApiKeysAuth) {
                return $authData;
            }
        }

        return null;
    }

    /**
     * @param FormBuilderInterface<ApiKeyToken|null> $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sfToken = $this->tokenStorage->getToken();
        if (!$sfToken instanceof TokenInterface) {
            return;
        }

        $symfonyUser = $sfToken->getUser();
        if (!$symfonyUser instanceof AbstractUser) {
            return;
        }

        $user = $symfonyUser->getWrappedUser();

        $builder->add(
            'name',
            TextType::class,
            [
                'required' => true,
                'label' => 'teknoo.east.common.api_keys.form.name',
                'attr' => [
                    'pattern' => '/^[a-z][a-z0-9\_\-]{3,}$/',
                ],
                'constraints' => [
                    new Regex(
                        pattern: '/^[a-z][a-z0-9\_\-]{3,}$/',
                        message: 'teknoo.east.common.api_keys.form.name.regex_error',
                    ),
                    new Callback(
                        callback: function (string $name, ExecutionContext $context, User $payload): void {
                            if ($this->findApiKeysAuth($payload)?->getToken($name)) {
                                $context->addViolation('teknoo.east.common.api_keys.error.already_exists');
                            }
                        },
                        payload: $user,
                    )
                ],
            ],
        );

        $builder->add(
            'expiresAt',
            DateType::class,
            [
                'required' => true,
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'teknoo.east.common.api_keys.form.expiration',
            ],
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($user): void {
                if (!$event->getForm()->isValid()) {
                    return;
                }

                $data = $event->getData();
                if (!$data instanceof ApiKeyToken) {
                    return;
                }

                $this->datesService->passMeTheDate(
                    function (DateTimeInterface $now) use ($user, $data): void {
                        $token = $this->tokenPrefix . bin2hex(random_bytes(32));

                        $data->setToken($token);
                        $data->setCreatedAt($now);
                        $data->setExpired(false);

                        $data->setTokenHash(
                            $this->passwordHasher->hashPassword(
                                new ApiKeysAuthUser(
                                    $user,
                                    $data,
                                ),
                                $token
                            )
                        );

                        $apiKeys = $this->findApiKeysAuth($user) ?? new ApiKeysAuth();
                        $apiKeys->addToken($data);
                        $user->addAuthData($apiKeys);
                    }
                );
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => ApiKeyToken::class,
        ]);
    }
}
