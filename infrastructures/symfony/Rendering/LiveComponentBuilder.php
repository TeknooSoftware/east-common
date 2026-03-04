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

namespace Teknoo\East\CommonBundle\Rendering;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\LiveComponent\EventListener\InterceptChildComponentRenderSubscriber;
use Symfony\UX\LiveComponent\LiveComponentHydrator;
use Symfony\UX\LiveComponent\Metadata\LiveComponentMetadataFactory;
use Symfony\UX\TwigComponent\ComponentFactory;
use Symfony\UX\TwigComponent\ComponentMetadata;
use Symfony\UX\TwigComponent\MountedComponent;
use Teknoo\East\Common\Contracts\Rendering\LiveComponentBuilderInterface;
use Throwable;

use function is_callable;
use function is_object;
use function property_exists;
use function trim;

/**
 * Contract to define rendering hook to manage live components
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LiveComponentBuilder implements LiveComponentBuilderInterface
{
    public function __construct(
        private ContainerInterface $container,
        private ?ComponentFactory $componentFactory = null,
        private ?LiveComponentHydrator $hydrator = null,
        private ?LiveComponentMetadataFactory $metadataFactory = null,
    ) {
    }

    private function buildComponentObject(
        ComponentMetadata $metadata,
    ): object {
        if ($this->container->has($metadata->getServiceId())) {
            $component = $this->container->get($metadata->getServiceId());
            if (is_object($component)) {
                return $component;
            }
        }

        $componentClass = $metadata->getClass();
        return new $componentClass();
    }

    /**
     * @param array<string, array<string, mixed>> $body
     */
    private function hydrateComponentObject(
        object $component,
        string $componentName,
        array $body,
    ): MountedComponent {
        $componentAttributes = $this->hydrator->hydrate(
            $component,
            $body['props'] ?? [],
            $body['updated'] ?? [],
            $this->metadataFactory->getMetadata($componentName),
            $body['propsFromParent'] ?? [],
        );

        $mountedComponent = new MountedComponent($componentName, $component, $componentAttributes);

        $mountedComponent->addExtraMetadata(
            InterceptChildComponentRenderSubscriber::CHILDREN_FINGERPRINTS_METADATA_KEY,
            $body['children'] ?? []
        );

        return $mountedComponent;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function updateComponentObject(
        object $component,
        array $parameters,
    ): void {
        foreach ($parameters as $parameterName => $parameterValue) {
            if (property_exists($component, $parameterName)) {
                $component->{$parameterName} = $parameterValue;
            }
        }
    }

    private function getComponentController(
        object $component,
        ComponentMetadata $metadata,
    ): callable {
        $defaultAction = trim((string) $metadata->get('default_action', '__invoke'), '()');

        if (is_callable([$component, $defaultAction])) {
            return [$component, $defaultAction];
        }

        throw new InvalidArgumentException('Missing Component Controller');
    }

    private function getSymfonyRequest(
        ServerRequestInterface $request,
    ): Request {
        $sfRequest = $request->getAttribute('request');

        if (!$sfRequest instanceof Request) {
            throw new InvalidArgumentException('Missing SymfonyRequest object.');
        }

        return $sfRequest;
    }

    public function buildComponent(
        ServerRequestInterface $request,
        string $componentName,
        array $parameters,
        array $body,
    ): bool {
        if (
            !$this->componentFactory
            || !$this->hydrator
            || !$this->metadataFactory
        ) {
            return false;
        }

        try {
            /** @var ComponentMetadata $metadata */
            $metadata = $this->componentFactory->metadataFor($componentName);

            $component = $this->buildComponentObject(
                metadata: $metadata,
            );

            $mountedComponent = $this->hydrateComponentObject(
                component: $component,
                componentName: $componentName,
                body: $body,
            );

            $this->updateComponentObject(
                component: $component,
                parameters: $parameters,
            );

            $sfRequest = $this->getSymfonyRequest($request);

            $sfRequest->attributes->set(
                '_controller',
                $this->getComponentController(
                    component: $component,
                    metadata: $metadata,
                )
            );

            $sfRequest->attributes->set('_mounted_component', $mountedComponent);
            $sfRequest->attributes->set('_component_default_action', true);
        } catch (Throwable $e) {
            throw new RuntimeException(
                message: 'Failed to build LiveComponent object.',
                previous: $e,
            );
        }

        return true;
    }
}
