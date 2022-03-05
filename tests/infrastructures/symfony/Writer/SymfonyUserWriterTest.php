<?php

/**
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

namespace Teknoo\Tests\East\WebsiteBundle\Writer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Teknoo\East\Website\Contracts\User\AuthDataInterface;
use Teknoo\East\Website\Object\StoredPassword;
use Teknoo\East\WebsiteBundle\Object\PasswordAuthenticatedUser;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\WebsiteBundle\Writer\SymfonyUserWriter;
use Teknoo\East\Website\Writer\UserWriter as UniversalWriter;
use Teknoo\East\Website\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Writer\SymfonyUserWriter
 */
class SymfonyUserWriterTest extends TestCase
{
    /**
     * @var UniversalWriter
     */
    private $universalWriter;

    /**
     * @var UserPasswordHasherInterface
     */
    private $userPasswordHasher;

    /**
     * @return UniversalWriter|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getUniversalWriter(): UniversalWriter
    {
        if (!$this->universalWriter instanceof UniversalWriter) {
            $this->universalWriter = $this->createMock(UniversalWriter::class);
        }

        return $this->universalWriter;
    }

    /**
     * @return UserPasswordHasherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getUserPasswordHasher(): UserPasswordHasherInterface
    {
        if (!$this->userPasswordHasher instanceof UserPasswordHasherInterface) {
            $this->userPasswordHasher = new class implements UserPasswordHasherInterface {
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

    public function testExceptionOnSaveWithBadPromise()
    {
        $this->expectException(\TypeError::class);
        $this->buildWriter()->save(new \stdClass(), new \stdClass());
    }

    public function testSaveWithWrongObject()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $object = $this->createMock(ObjectInterface::class);

        $this->getUniversalWriter()
            ->expects(self::never())
            ->method('save');

        $promise->expects(self::once())
            ->method('fail');

        self::assertInstanceOf(
            SymfonyUserWriter::class,
            $this->buildWriter()->save($object, $promise)
        );
    }

    public function testSaveWithUserWithNoStoredPassword()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $user = $this->createMock(BaseUser::class);
        $authData = $this->createMock(AuthDataInterface::class);
        $user->expects(self::any())
            ->method('getAuthData')
            ->willReturn([$authData]);

        $this->getUniversalWriter()
            ->expects(self::once())
            ->method('save')
            ->with($user, $promise)
            ->willReturnSelf();

        self::assertInstanceOf(
            SymfonyUserWriter::class,
            $this->buildWriter()->save($user, $promise)
        );
    }

    public function testSaveWithUserWithUpdatedPassword()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $user = $this->createMock(BaseUser::class);
        $storedPassword = $this->createMock(StoredPassword::class);

        $user->expects(self::any())
            ->method('getAuthData')
            ->willReturn([$storedPassword]);

        $storedPassword->expects(self::once())
            ->method('mustHashPassword')
            ->willReturn(true);

        $storedPassword->expects(self::once())
            ->method('setHashedPassword')
            ->with('fooBar')
            ->willReturnSelf();

        $storedPassword->expects(self::once())
            ->method('setAlgo')
            ->with(PasswordAuthenticatedUser::class)
            ->willReturnSelf();

        $this->getUniversalWriter()
            ->expects(self::once())
            ->method('save')
            ->with($user, $promise)
            ->willReturnSelf();

        self::assertInstanceOf(
            SymfonyUserWriter::class,
            $this->buildWriter()->save($user, $promise)
        );
    }

    public function testSaveWithUserWithUpdatedHashedPassword()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $user = $this->createMock(BaseUser::class);
        $storedPassword = $this->createMock(StoredPassword::class);

        $user->expects(self::any())
            ->method('getAuthData')
            ->willReturn([$storedPassword]);

        $storedPassword->expects(self::once())
            ->method('mustHashPassword')
            ->willReturn(false);

        $storedPassword->expects(self::never())
            ->method('eraseCredentials');

        $storedPassword->expects(self::never())
            ->method('setPassword')
            ->with('fooBar')
            ->willReturnSelf();

        $this->getUniversalWriter()
            ->expects(self::once())
            ->method('save')
            ->with($user, $promise)
            ->willReturnSelf();

        self::assertInstanceOf(
            SymfonyUserWriter::class,
            $this->buildWriter()->save($user, $promise)
        );
    }

    public function testRemove()
    {
        $object = $this->createMock(ObjectInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $this->getUniversalWriter()
            ->expects(self::once())
            ->method('remove')
            ->with($object, $promise)
            ->willReturnSelf();

        self::assertInstanceOf(
            SymfonyUserWriter::class,
            $this->buildWriter()->remove($object, $promise)
        );
    }
}
