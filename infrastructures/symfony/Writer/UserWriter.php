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

namespace Teknoo\East\WebsiteBundle\Writer;

use RuntimeException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\Contracts\ObjectInterface;
use Teknoo\East\Website\Object\User as BaseUser;
use Teknoo\East\Website\Writer\WriterInterface;
use Teknoo\East\Website\Writer\UserWriter as UniversalWriter;
use Teknoo\East\WebsiteBundle\Object\User;

/**
 * East Website writer to manager persistent operations on Symfony version of East Website's User class.
 * This writer is able to manage users'passwords, and hash them before persist data via Symfony's hashers.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class UserWriter implements WriterInterface
{
    public function __construct(
        private UniversalWriter $universalWriter,
        private EncoderFactoryInterface $encoderFactory, //todo deprecated
    ) {
    }

    private function encodePassword(BaseUser $user): void
    {
        if ($user->hasUpdatedPassword()) {
            $encoder = $this->encoderFactory->getEncoder(new User($user));
            $salt = $user->getSalt();
            $user->setPassword((string) $encoder->encodePassword($user->getPassword(), $salt));
        } else {
            $user->eraseCredentials();
        }
    }

    public function save(ObjectInterface $object, PromiseInterface $promise = null): WriterInterface
    {
        if (!$object instanceof BaseUser) {
            if (null !== $promise) {
                $class = $object::class;
                $promise->fail(new RuntimeException("The class $class is not managed by this writer"));
            }

            return $this;
        }

        $this->encodePassword($object);

        $this->universalWriter->save($object, $promise);

        return $this;
    }

    public function remove(ObjectInterface $object, PromiseInterface $promise = null): WriterInterface
    {
        $this->universalWriter->remove($object, $promise);

        return $this;
    }
}
