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

namespace Teknoo\East\WebsiteBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Teknoo\East\Website\Object\User as BaseUser;
use Teknoo\East\Website\Writer\UserWriter;
use Teknoo\East\WebsiteBundle\Object\User;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class CreateUserCommand extends Command
{
    /**
     * @var UserWriter
     */
    private $writer;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * CreateUserCommand constructor.
     * @param UserWriter $writer
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(UserWriter $writer, EncoderFactoryInterface $encoderFactory)
    {
        $this->writer = $writer;
        $this->encoderFactory = $encoderFactory;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('teknoo:website:create-admin')
            ->setDescription('Creates a new admin user in the website package')
            ->addArgument('email', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addArgument('password', InputArgument::REQUIRED, 'Your password?')
            ->addArgument('last_name', InputArgument::OPTIONAL, 'Your last name?')
            ->addArgument('first_name', InputArgument::OPTIONAL, 'Your first name?');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = new BaseUser();

        $user->setEmail($input->getArgument('email'));
        $user->setFirstName($input->getArgument('first_name'));
        $user->setLastName($input->getArgument('last_name'));
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        $encoder = $this->encoderFactory->getEncoder(new User($user));
        $salt = $user->getSalt();
        $user->setPassword($encoder->encodePassword($input->getArgument('password'), $salt));

        $this->writer->save($user);

        $output->writeln('User created');
    }
}
