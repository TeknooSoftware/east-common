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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\CommonBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User as BaseUser;
use Teknoo\East\Common\Writer\UserWriter;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;

/**
 * Symfony App Console to put a new administrator into the Website's database. By example, to use to create the first
 * administrator to configure it online.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly UserWriter $writer,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('teknoo:common:create-admin')
            ->setDescription('Creates a new admin user in the common package')
            ->addArgument('email', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('password', InputArgument::REQUIRED, 'Your password?')
            ->addArgument('last_name', InputArgument::OPTIONAL, 'Your last name?')
            ->addArgument('first_name', InputArgument::OPTIONAL, 'Your first name?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new BaseUser();

        $user->setEmail((string) $input->getArgument('email'));
        $user->setFirstName((string) $input->getArgument('first_name'));
        $user->setLastName((string) $input->getArgument('last_name'));
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        $storedPassword = new StoredPassword();
        $storedPassword->setAlgo(PasswordAuthenticatedUser::class);
        $storedPassword->setHashedPassword(
            $this->passwordHasher->hashPassword(
                new PasswordAuthenticatedUser(
                    $user,
                    $storedPassword
                ),
                (string) $input->getArgument('password')
            )
        );

        $user->addAuthData($storedPassword);

        $this->writer->save($user);

        $output->writeln('User created');

        return 0;
    }
}
