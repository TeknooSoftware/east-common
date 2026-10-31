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

use SensitiveParameter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
use Teknoo\East\Common\Contracts\User\ApiKeyFactoryInterface;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\CommonBundle\Security\ApiKeyFactory;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

use function array_combine;

/**
 * Symfony form to create a new API key (an ApiKeyToken instance) for the current authenticated user : the form asks
 * the name of the key and its expiration date, and its scope when the option `scopes` lists scopes. When the form is
 * valid, the key factory (`ApiKeyFactoryInterface`) generates the token of the key (with an optional prefix), its
 * digest and its hash, and adds the key to the ApiKeysAuth instance of the user.
 * The token is available, into the ApiKeyToken instance, only during this request : it is not persisted.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiKeysAuthType extends AbstractType
{
    private readonly ApiKeyFactoryInterface $apiKeyFactory;

    /**
     * @param array<int, string> $scopes default choices of the field `scope`, without scope when it is empty
     */
    public function __construct(
        DatesService $datesService,
        private readonly TokenStorageInterface $tokenStorage,
        UserPasswordHasherInterface $passwordHasher,
        string $tokenPrefix = '',
        private readonly array $scopes = [],
        bool $preferRealDate = false,
        ?ApiKeyFactoryInterface $apiKeyFactory = null,
    ) {
        $this->apiKeyFactory = $apiKeyFactory ?? new ApiKeyFactory(
            $datesService,
            $passwordHasher,
            $tokenPrefix,
            $scopes,
            $preferRealDate,
        );
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
                    //HTML pattern: anchored implicitly, without delimiters, and valid with the `v` flag of browsers
                    'pattern' => '[a-z][a-z0-9_\-]{3,}',
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

        /** @var array<int, string> $scopes */
        $scopes = $options['scopes'] ?? [];
        if ([] !== $scopes) {
            $builder->add(
                'scope',
                ChoiceType::class,
                [
                    'required' => true,
                    'label' => 'teknoo.east.common.api_keys.form.scope',
                    'choices' => array_combine($scopes, $scopes),
                ],
            );
        }

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

                /** @var Promise<ApiKeyToken, mixed, mixed> $promise */
                $promise = new Promise(
                    null,
                    static function (#[SensitiveParameter] Throwable $error): never {
                        throw $error;
                    },
                );

                $this->apiKeyFactory->create($user, $data, $promise);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => ApiKeyToken::class,
            'scopes' => $this->scopes,
        ]);

        $resolver->setAllowedTypes('scopes', 'string[]');
    }
}
