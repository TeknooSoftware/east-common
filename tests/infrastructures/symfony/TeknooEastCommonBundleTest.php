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

namespace Teknoo\Tests\East\CommonBundle;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Teknoo\East\CommonBundle\DependencyInjection\Security\Factory\ApiKeyAuthenticatorFactory;
use Teknoo\East\CommonBundle\TeknooEastCommonBundle;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(TeknooEastCommonBundle::class)]
class TeknooEastCommonBundleTest extends TestCase
{
    public function testBuildAddsTheApiKeyAuthenticatorFactory(): void
    {
        $extension = $this->createMock(SecurityExtension::class);
        $extension->method('getAlias')->willReturn('security');
        $extension->expects($this->once())
            ->method('addAuthenticatorFactory')
            ->with($this->isInstanceOf(ApiKeyAuthenticatorFactory::class));

        $container = new ContainerBuilder();
        $container->registerExtension($extension);

        new TeknooEastCommonBundle()->build($container);
    }

    public function testBuildWithoutSecurity(): void
    {
        $container = new ContainerBuilder();

        new TeknooEastCommonBundle()->build($container);

        $this->assertFalse($container->hasExtension('security'));
    }
}
