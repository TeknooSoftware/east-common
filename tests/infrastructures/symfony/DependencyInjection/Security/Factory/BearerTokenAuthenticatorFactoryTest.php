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

namespace Teknoo\Tests\East\CommonBundle\DependencyInjection\Security\Factory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Teknoo\East\CommonBundle\DependencyInjection\Security\Factory\BearerTokenAuthenticatorFactory;
use Teknoo\East\CommonBundle\Security\Authenticator\BearerTokenAuthenticator;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(BearerTokenAuthenticatorFactory::class)]
class BearerTokenAuthenticatorFactoryTest extends TestCase
{
    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function processConfiguration(array $config): array
    {
        $node = new ArrayNodeDefinition('bearer_token');
        new BearerTokenAuthenticatorFactory()->addConfiguration($node);

        return new Processor()->process($node->getNode(), [$config]);
    }

    public function testGetPriority(): void
    {
        $this->assertSame(-40, new BearerTokenAuthenticatorFactory()->getPriority());
    }

    public function testGetKey(): void
    {
        $this->assertSame('bearer_token', new BearerTokenAuthenticatorFactory()->getKey());
    }

    public function testAddConfiguration(): void
    {
        $this->assertSame(
            [
                'second_header' => 'x-api-key',
                'failure_handler' => 'my_failure_handler',
            ],
            $this->processConfiguration(
                [
                    'second_header' => 'x-api-key',
                    'failure_handler' => 'my_failure_handler',
                ]
            ),
        );
    }

    public function testAddConfigurationWithDefaultValues(): void
    {
        $this->assertSame(
            [
                'failure_handler' => 'my_failure_handler',
                'second_header' => null,
            ],
            $this->processConfiguration(['failure_handler' => 'my_failure_handler']),
        );
    }

    public function testAddConfigurationWithoutFailureHandler(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfiguration([]);
    }

    public function testCreateAuthenticator(): void
    {
        $container = new ContainerBuilder();

        $id = new BearerTokenAuthenticatorFactory()->createAuthenticator(
            $container,
            'api',
            [
                'second_header' => 'x-api-key',
                'failure_handler' => 'my_failure_handler',
            ],
            'with_api_key',
        );

        $this->assertSame('teknoo.east.common.security.authenticator.bearer_token.api', $id);

        $definition = $container->getDefinition($id);
        $this->assertInstanceOf(ChildDefinition::class, $definition);
        $this->assertSame(BearerTokenAuthenticator::class, $definition->getParent());
        $this->assertEquals(new Reference('my_failure_handler'), $definition->getArgument(2));
        $this->assertSame('x-api-key', $definition->getArgument(3));
    }

    public function testCreateAuthenticatorWithoutFailureHandler(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        new BearerTokenAuthenticatorFactory()->createAuthenticator(
            new ContainerBuilder(),
            'api',
            ['second_header' => null],
            'with_api_key',
        );
    }
}
