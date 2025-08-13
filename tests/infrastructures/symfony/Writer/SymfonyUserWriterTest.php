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

namespace Teknoo\Tests\East\CommonBundle\Writer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User as BaseUser;
use Teknoo\East\Common\Writer\UserWriter as UniversalWriter;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(SymfonyUserWriter::class)]
class SymfonyUserWriterTest extends TestCase
{
    private (UniversalWriter&MockObject)|null $universalWriter = null;

    private ?UserPasswordHasherInterface $userPasswordHasher = null;

    public function getUniversalWriter(): UniversalWriter&MockObject
    {
        if (!$this->universalWriter instanceof UniversalWriter) {
            $this->universalWriter = $this->createMock(UniversalWriter::class);
        }

        return $this->universalWriter;
    }

    public function getUserPasswordHasher(): UserPasswordHasherInterface
    {
        if (!$this->userPasswordHasher instanceof UserPasswordHasherInterface) {
            $this->userPasswordHasher = new class () implements UserPasswordHasherInterface {
                public function hashPassword(PasswordAuthenticatedUserInterface $user, string $plainPassword): string
                {
                    return 'fooBar';
                }

                public function isPasswordValid(PasswordAuthenticatedUserInterface $user, string $plainPassword): bool
                {
                }

                public function needsRehash(PasswordAuthenticatedUserInterface $user): bool
                {
                }
            };
        }

        return $this->userPasswordHasher;
    }

    public function buildWriter(): SymfonyUserWriter
    {
        return new SymfonyUserWriter(
            $this->getUniversalWriter(),
            $this->getUserPasswordHasher()
        );
    }

    public function testExceptionOnSaveWithBadPromise(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildWriter()->save(new \stdClass(), new \stdClass());
    }

    public function testSaveWithWrongObject(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $this->getUniversalWriter()
            ->expects($this->never())
            ->method('save');

        $promise->expects($this->once())
            ->method('fail');

        $this->assertInstanceOf(
            SymfonyUserWriter::class,
            $this->buildWriter()->save($object, $promise)
        );
    }

    public function testSaveWithUserWithNoStoredPassword(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $user = $this->createMock(BaseUser::class);
        $authData = $this->createMock(AuthDataInterface::class);
        $user
            ->method('getAuthData')
            ->willReturn([$authData]);

        $this->getUniversalWriter()
            ->expects($this->once())
            ->method('save')
            ->with($user, $promise)
            ->willReturnSelf();

        $this->assertInstanceOf(
            SymfonyUserWriter::class,
            $this->buildWriter()->save($user, $promise)
        );
    }

    public function testSaveWithUserWithUpdatedPassword(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $user = $this->createMock(BaseUser::class);
        $storedPassword = $this->createMock(StoredPassword::class);

        $user
            ->method('getAuthData')
            ->willReturn([$storedPassword]);

        $storedPassword->expects($this->once())
            ->method('mustHashPassword')
            ->willReturn(true);

        $storedPassword->expects($this->once())
            ->method('setHashedPassword')
            ->with('fooBar')
            ->willReturnSelf();

        $storedPassword->expects($this->once())
            ->method('setAlgo')
            ->with(PasswordAuthenticatedUser::class)
            ->willReturnSelf();

        $this->getUniversalWriter()
            ->expects($this->once())
            ->method('save')
            ->with($user, $promise)
            ->willReturnSelf();

        $this->assertInstanceOf(
            SymfonyUserWriter::class,
            $this->buildWriter()->save($user, $promise)
        );
    }

    public function testSaveWithUserWithUpdatedHashedPassword(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $user = $this->createMock(BaseUser::class);
        $storedPassword = $this->createMock(StoredPassword::class);

        $user
            ->method('getAuthData')
            ->willReturn([$storedPassword]);

        $storedPassword->expects($this->once())
            ->method('mustHashPassword')
            ->willReturn(false);

        $storedPassword->expects($this->never())
            ->method('eraseCredentials');

        $storedPassword->expects($this->never())
            ->method('setPassword')
            ->with('fooBar')
            ->willReturnSelf();

        $this->getUniversalWriter()
            ->expects($this->once())
            ->method('save')
            ->with($user, $promise)
            ->willReturnSelf();

        $this->assertInstanceOf(
            SymfonyUserWriter::class,
            $this->buildWriter()->save($user, $promise)
        );
    }

    public function testRemove(): void
    {
        $object = $this->createMock(IdentifiedObjectInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $this->getUniversalWriter()
            ->expects($this->once())
            ->method('remove')
            ->with($object, $promise)
            ->willReturnSelf();

        $this->assertInstanceOf(
            SymfonyUserWriter::class,
            $this->buildWriter()->remove($object, $promise)
        );
    }
}
