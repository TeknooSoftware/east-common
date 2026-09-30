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

namespace Teknoo\Tests\East\Common\Recipe\Step\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Recipe\Step\User\RemoveApiKey;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(RemoveApiKey::class)]
class RemoveApiKeyTest extends TestCase
{
    public function buildStep(): RemoveApiKey
    {
        return new RemoveApiKey();
    }

    public function testInvokeWithExistingApiKeysAuth(): void
    {
        $apiKeys = new ApiKeysAuth([new ApiKeyToken('to-remove'), new ApiKeyToken('to-keep')]);
        $user = new User()->setAuthData([new StoredPassword(), $apiKeys]);

        $step = $this->buildStep();
        $this->assertSame(
            $step,
            $step(
                user: $user,
                tokenName: 'to-remove',
            ),
        );

        $this->assertNotInstanceOf(ApiKeyToken::class, $apiKeys->getToken('to-remove'));
        $this->assertInstanceOf(ApiKeyToken::class, $apiKeys->getToken('to-keep'));
    }

    public function testInvokeWithAnApiKeysAuthSubclass(): void
    {
        $apiKeys = new class ([new ApiKeyToken('to-remove')]) extends ApiKeysAuth {
        };
        $user = new User()->setAuthData([$apiKeys]);

        $this->buildStep()(
            user: $user,
            tokenName: 'to-remove',
        );

        $this->assertNotInstanceOf(ApiKeyToken::class, $apiKeys->getToken('to-remove'));
    }

    public function testInvokeWithoutExistingApiKeysAuth(): void
    {
        $user = new User()->setAuthData([new StoredPassword()]);

        $step = $this->buildStep();
        $this->assertSame(
            $step,
            $step(
                user: $user,
                tokenName: 'whatever',
            ),
        );

        //No ApiKeysAuth is added to the user
        $this->assertCount(1, [...$user->getAuthData()]);
    }
}
