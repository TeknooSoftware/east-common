<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
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

namespace Teknoo\East\CommonBundle\Writer;

use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User as BaseUser;
use Teknoo\East\Common\Writer\UserWriter as UniversalWriter;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * East Common writer to manager persistent operations on Symfony version of East Common's User class.
 * This writer is able to manage users'passwords, and hash them before persist data via Symfony's hashers.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements WriterInterface<BaseUser>
 */
class SymfonyUserWriter implements WriterInterface
{
    public function __construct(
        private readonly UniversalWriter $universalWriter,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    private function hashPassword(BaseUser $user, StoredPassword $password): void
    {
        $password->setAlgo(PasswordAuthenticatedUser::class);

        $password->setHashedPassword(
            $this->passwordHasher->hashPassword(
                new PasswordAuthenticatedUser($user, $password),
                $password->getHash()
            )
        );
    }

    public function save(
        ObjectInterface $object,
        PromiseInterface $promise = null,
        ?bool $prefereRealDateOnUpdate = null,
    ): WriterInterface {
        if (!$object instanceof BaseUser) {
            if (null !== $promise) {
                $objectClass = $object::class;
                $promise->fail(
                    new RuntimeException("The class $objectClass is not managed by this writer", 500)
                );
            }

            return $this;
        }

        foreach ($object->getAuthData() as $authData) {
            if (!$authData instanceof StoredPassword) {
                continue;
            }

            if ($authData->mustHashPassword()) {
                $this->hashPassword($object, $authData);
            }
        }

        $this->universalWriter->save($object, $promise, $prefereRealDateOnUpdate);

        return $this;
    }

    public function remove(ObjectInterface $object, PromiseInterface $promise = null): WriterInterface
    {
        $this->universalWriter->remove($object, $promise);

        return $this;
    }
}
