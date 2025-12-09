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

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Teknoo\East\CommonBundle\Contracts\Object\UserWithTOTPAuthInterface;
use Teknoo\East\CommonBundle\Form\Model\TOTP;
use Teknoo\East\CommonBundle\Recipe\Step\UserTrait;

/**
 * Symfony form able to handle 2FA TOTP/Google Auth code input by an user and check it with an
 * authenticator
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class TOTPType extends AbstractType
{
    use UserTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ?TotpAuthenticatorInterface $totpAuthenticator,
        private readonly ?GoogleAuthenticatorInterface $googleAuthenticator,
    ) {
    }

    /**
     * @param FormBuilderInterface<TOTP> $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'code',
            TextType::class,
            [
                'constraints' => [
                    new Callback(
                        function (string $code, ExecutionContextInterface $context): void {
                            $validated = false;
                            $user = $this->getUser();

                            if (
                                $user instanceof GoogleTwoFactorInterface
                                && $user instanceof UserWithTOTPAuthInterface
                                && $user->getTOTPAuth()
                            ) {
                                $validated = $this->googleAuthenticator?->checkCode($user, $code);
                            }

                            if (
                                $user instanceof TotpTwoFactorInterface
                                && $user instanceof UserWithTOTPAuthInterface
                                && $user->getTOTPAuth()
                            ) {
                                $validated = $this->totpAuthenticator?->checkCode($user, $code);
                            }

                            if (true !== $validated) {
                                $context->buildViolation('teknoo.east.common.2fa.error_code_not_validated')
                                    ->atPath('code')
                                    ->addViolation();
                            }
                        }
                    ),
                ]
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => TOTP::class,
        ]);
    }
}
