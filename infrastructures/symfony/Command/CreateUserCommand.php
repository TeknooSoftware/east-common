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

namespace Teknoo\East\WebsiteBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Teknoo\East\Website\Object\StoredPassword;
use Teknoo\East\Website\Object\User as BaseUser;
use Teknoo\East\Website\Writer\UserWriter;
use Teknoo\East\WebsiteBundle\Object\PasswordAuthenticatedUser;

/**
 * Symfony App Console to put a new administrator into the Website's database. By example, to use to create the first
 * administrator to configure it online.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class CreateUserCommand extends Command
{
    public function __construct(
        private UserWriter $writer,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('teknoo:website:create-admin')
            ->setDescription('Creates a new admin user in the website package')
            ->addArgument('email', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('password', InputArgument::REQUIRED, 'Your password?')
            ->addArgument('last_name', InputArgument::OPTIONAL, 'Your last name?')
            ->addArgument('first_name', InputArgument::OPTIONAL, 'Your first name?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = new BaseUser();

        $user->setEmail((string) $input->getArgument('email'));
        $user->setFirstName((string) $input->getArgument('first_name'));
        $user->setLastName((string) $input->getArgument('last_name'));
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        $storedPassword = new StoredPassword();
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
