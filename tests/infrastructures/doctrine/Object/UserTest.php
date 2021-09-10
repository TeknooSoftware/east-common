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

namespace Teknoo\Tests\East\Website\Doctrine\Object;

use Teknoo\East\Website\Object\StoredPassword;
use Teknoo\East\Website\Object\ThirdPartyAuth;
use Teknoo\Tests\East\Website\Object\UserTest as OriginalTest;
use Teknoo\East\Website\Doctrine\Object\User;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Doctrine\Object\User
 */
class UserTest extends OriginalTest
{
    /**
     * @return User
     */
    public function buildObject(): User
    {
        return new User();
    }

    public function testMigrateSaltWithEmptyValue()
    {
        $user = $this->buildObject();
        self::assertInstanceOf(
            User::class,
            $user->migrateSalt('')->postLoad()
        );

        self::assertEmpty($user->getAuthData());
    }

    public function testMigrateSaltNonMigrated()
    {
        $user = $this->buildObject();
        self::assertInstanceOf(
            User::class,
            $user->migrateSalt('foo')->postLoad()
        );

        self::assertNotEmpty($user->getAuthData());
        self::assertEquals(
            'foo',
            $user->getAuthData()[0]->getSalt()
        );
    }

    public function testMigrateSaltNonMigratedWithAnotherAuthData()
    {
        $user = $this->buildObject()->setAuthData([new ThirdPartyAuth()]);
        self::assertInstanceOf(
            User::class,
            $user->migrateSalt('foo')->postLoad()
        );

        self::assertNotEmpty($user->getAuthData());
        self::assertEquals(
            'foo',
            $user->getAuthData()[1]->getSalt()
        );
    }

    public function testMigrateSaltMigrated()
    {
        $user = $this->buildObject()->setAuthData([
            (new StoredPassword())->setSalt('bar')
        ]);
        self::assertInstanceOf(
            User::class,
            $user->migrateSalt('foo')->postLoad()
        );

        self::assertNotEmpty($user->getAuthData());
        self::assertEquals(
            'bar',
            $user->getAuthData()[0]->getSalt()
        );
    }

    public function testMigrateSaltUserUseModernAlgo()
    {

        $user = $this->buildObject()->setAuthData([
            (new StoredPassword())->setAlgo('sodium')
        ]);
        self::assertInstanceOf(
            User::class,
            $user->migrateSalt('foo')->postLoad()
        );

        self::assertNotEmpty($user->getAuthData());
        self::assertEquals(
            '',
            $user->getAuthData()[0]->getSalt()
        );
        self::assertEquals(
            'sodium',
            $user->getAuthData()[0]->getAlgo()
        );
    }

    public function testMigrateHashWithEmptyValue()
    {
        $user = $this->buildObject();
        self::assertInstanceOf(
            User::class,
            $user->migrateHash('')->postLoad()
        );

        self::assertEmpty($user->getAuthData());
    }

    public function testMigrateHashNonMigrated()
    {
        $user = $this->buildObject();
        self::assertInstanceOf(
            User::class,
            $user->migrateHash('foo')->postLoad()
        );

        self::assertNotEmpty($user->getAuthData());
        self::assertEquals(
            'foo',
            $user->getAuthData()[0]->getHash()
        );
        self::assertFalse($user->getAuthData()[0]->mustHashPassword());
    }

    public function testMigrateHashNonMigratedWithAnotherAuthData()
    {
        $user = $this->buildObject()->setAuthData([new ThirdPartyAuth()]);
        self::assertInstanceOf(
            User::class,
            $user->migrateHash('foo')->postLoad()
        );

        self::assertNotEmpty($user->getAuthData());
        self::assertEquals(
            'foo',
            $user->getAuthData()[1]->getHash()
        );
        self::assertFalse($user->getAuthData()[1]->mustHashPassword());
    }

    public function testMigrateHashMigrated()
    {
        $user = $this->buildObject()->setAuthData([
            (new StoredPassword())->setHashedPassword('bar')
        ]);
        self::assertInstanceOf(
            User::class,
            $user->migrateHash('foo')->postLoad()
        );

        self::assertNotEmpty($user->getAuthData());
        self::assertEquals(
            'bar',
            $user->getAuthData()[0]->getHash()
        );
        self::assertFalse($user->getAuthData()[0]->mustHashPassword());
    }

    public function testMigrateHashUserUseModernAlgo()
    {

        $user = $this->buildObject()->setAuthData([
            (new StoredPassword())->setHashedPassword('bar')->setAlgo('sodium')
        ]);
        self::assertInstanceOf(
            User::class,
            $user->migrateHash('foo')->postLoad()
        );

        self::assertNotEmpty($user->getAuthData());
        self::assertEquals(
            'bar',
            $user->getAuthData()[0]->getHash()
        );
        self::assertEquals(
            'sodium',
            $user->getAuthData()[0]->getAlgo()
        );
        self::assertFalse($user->getAuthData()[0]->mustHashPassword());
    }
}
