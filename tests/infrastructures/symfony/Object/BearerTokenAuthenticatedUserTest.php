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

namespace Teknoo\Tests\East\CommonBundle\Object;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\BearerToken;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\CommonBundle\Object\BearerTokenAuthenticatedUser;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(BearerTokenAuthenticatedUser::class)]
#[CoversClass(AbstractUser::class)]
class BearerTokenAuthenticatedUserTest extends TestCase
{
    public function testGetWrappedUserAndBearerToken(): void
    {
        $user = new User()->setEmail('foo@bar')->setRoles(['ROLE_USER']);
        $bearerToken = new BearerToken(name: 'foo', tokenDigest: 'digest');

        $object = new BearerTokenAuthenticatedUser($user, $bearerToken);

        $this->assertSame($user, $object->getWrappedUser());
        $this->assertSame($bearerToken, $object->getWrappedBearerToken());
        $this->assertSame('foo@bar', $object->getUserIdentifier());
        $this->assertSame(['ROLE_USER'], $object->getRoles());
    }

    public function testEraseCredentials(): void
    {
        $storedPassword = new StoredPassword(hash: 'secret');
        $user = new User()->setEmail('foo@bar')->setAuthData([$storedPassword]);
        $bearerToken = new BearerToken(name: 'foo', tokenDigest: 'digest');

        $object = new BearerTokenAuthenticatedUser($user, $bearerToken);
        $object->eraseCredentials();

        //The digest of the key is not erased
        $this->assertSame('digest', $object->getWrappedBearerToken()->getTokenDigest());
    }
}
