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

namespace Teknoo\East\CommonBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Teknoo\East\CommonBundle\Security\Authenticator\ApiKeyAuthenticator;

use function is_string;

/**
 * Factory of Symfony Security to configure `ApiKeyAuthenticator` in a firewall, with the key `api_key` and its options
 * `second_header` (optional, a second header with the API key), `accepted_scopes` (optional, without it the scope is
 * not checked) and `failure_handler` (required, the service which renders failures).
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiKeyAuthenticatorFactory implements AuthenticatorFactoryInterface
{
    private const int PRIORITY = -40;

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    public function getKey(): string
    {
        return 'api_key';
    }

    public function addConfiguration(NodeDefinition $builder): void
    {
        /** @var ArrayNodeDefinition $builder */
        $builder
            ->children()
                ->scalarNode('second_header')->defaultNull()->end()
                ->arrayNode('accepted_scopes')->scalarPrototype()->end()->defaultValue([])->end()
                ->scalarNode('failure_handler')->isRequired()->cannotBeEmpty()->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $config
     */
    public function createAuthenticator(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        string $userProviderId,
    ): string {
        $failureHandler = $config['failure_handler'] ?? null;
        if (!is_string($failureHandler)) {
            throw new InvalidConfigurationException(
                "The option `failure_handler` of the API key authenticator of the firewall $firewallName is missing",
            );
        }

        $authenticatorId = 'teknoo.east.common.security.authenticator.api_key.' . $firewallName;

        $container->setDefinition($authenticatorId, new ChildDefinition(ApiKeyAuthenticator::class))
            ->replaceArgument(2, new Reference($failureHandler))
            ->replaceArgument(3, $config['second_header'])
            ->replaceArgument(4, $config['accepted_scopes']);

        return $authenticatorId;
    }
}
