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
use PropertyHookType;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\EventListener\InterceptChildComponentRenderSubscriber;
use Symfony\UX\LiveComponent\LiveComponentHydrator;
use Symfony\UX\LiveComponent\Metadata\LiveComponentMetadataFactory;
use Symfony\UX\TwigComponent\ComponentFactory;
use Symfony\UX\TwigComponent\ComponentMetadata;
use Symfony\UX\TwigComponent\MountedComponent;
use Teknoo\East\Common\Contracts\Rendering\LiveComponentBuilderInterface;
use Teknoo\East\CommonBundle\Contracts\Rendering\ComponentFactoryInterface;
use Teknoo\East\CommonBundle\Contracts\Rendering\LiveComponentHydratorInterface;
use Teknoo\East\CommonBundle\Contracts\Rendering\LiveComponentMetadataFactoryInterface;
use Throwable;

use function is_callable;
use function is_object;
use function is_string;
use function method_exists;
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
        private null|ComponentFactory|ComponentFactoryInterface $componentFactory = null,
        private null|LiveComponentHydrator|LiveComponentHydratorInterface $hydrator = null,
        private null|LiveComponentMetadataFactory|LiveComponentMetadataFactoryInterface $metadataFactory = null,
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

    private function isWritableProperty(object $component, string $propertyName): bool
    {
        if (!property_exists($component, $propertyName)) {
            return false;
        }

        $property = new ReflectionProperty($component, $propertyName);

        return $property->isPublic()
            && !$property->isStatic()
            && !$property->isReadOnly()
            && !$property->isPrivateSet()
            && !$property->isProtectedSet()
            && (!$property->isVirtual() || $property->hasHook(PropertyHookType::Set));
    }

    /**
     * Only public properties writable from outside of the component are updated: private, protected, static and
     * read-only properties (like the form of `ComponentWithFormTrait`) are ignored.
     *
     * @param array<string, mixed> $parameters
     */
    private function updateComponentObject(
        object $component,
        array $parameters,
    ): void {
        foreach ($parameters as $parameterName => $parameterValue) {
            if ($this->isWritableProperty($component, $parameterName)) {
                $component->{$parameterName} = $parameterValue;
            }
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function getComponentController(
        object $component,
        ComponentMetadata $metadata,
        Request $sfRequest,
        array &$parameters,
        bool &$isDefaultAction,
    ): callable {
        $action = $this->getLiveAction(
            component: $component,
            sfRequest: $sfRequest,
            parameters: $parameters,
        );

        if (null === $action) {
            $action = trim((string) $metadata->get('default_action', '__invoke'), '()');

            $isDefaultAction = true;
        }

        if (is_callable([$component, $action])) {
            return [$component, $action];
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

    /**
     * The action of the Live request, when it must run instead of the default action of the component: a public method
     * with the attribute `LiveAction`, as Symfony UX requires. Any other action keeps the default action, like the
     * `save` sent by a search form without method. The header of the Live requests is checked here, Symfony UX refuses
     * it after the controller is chosen, too late under East. An action is also refused after an error of the recipe:
     * the access rules of an object are steps of this recipe.
     *
     * @param array<string, mixed> $parameters
     */
    private function getLiveAction(
        object $component,
        Request $sfRequest,
        array &$parameters,
    ): ?string {
        $action = $sfRequest->attributes->get('_live_action');

        if (
            !is_string($action)
            || 'get' === $action
            || !method_exists($component, $action)
            || 'XMLHttpRequest' !== $sfRequest->headers->get('X-Requested-With')
            || ($parameters['error'] ?? null) instanceof Throwable
        ) {
            return null;
        }

        $method = new ReflectionMethod($component, $action);
        if (!$method->isPublic() || empty($method->getAttributes(LiveAction::class))) {
            return null;
        }

        return $action;
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

            $isDefaultAction = false;
            $sfRequest->attributes->set(
                '_controller',
                $this->getComponentController(
                    component: $component,
                    metadata: $metadata,
                    sfRequest: $sfRequest,
                    parameters: $parameters,
                    isDefaultAction: $isDefaultAction,
                )
            );

            $sfRequest->attributes->set('_mounted_component', $mountedComponent);
            if ($isDefaultAction) {
                $sfRequest->attributes->set('_component_default_action', true);
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                message: 'Failed to build LiveComponent object.',
                previous: $e,
            );
        }

        return true;
    }
}
