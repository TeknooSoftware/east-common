<?php
/**
 * Created by PhpStorm.
 * Author : Richard Déloge, richarddeloge@gmail.com, https://teknoo.software
 * Date: 04/03/2026
 * Time: 10:33
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
use function property_exists;

class LiveComponentBuilder implements LiveComponentBuilderInterface
{
    public function __construct(
        private ?ComponentFactory $componentFactory = null,
        private ?LiveComponentHydrator $hydrator = null,
        private ?LiveComponentMetadataFactory $metadataFactory = null,
        private ContainerInterface $container,
    ) {
    }

    private function buildComponentObject(
        ComponentMetadata $metadata,
    ): object {
        if ($this->container->has($metadata->getServiceId())) {
            return $this->container->get($metadata->getServiceId());
        }

        $componentClass = $metadata->getClass();
        return new $componentClass();
    }

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
        $defaultAction = trim($metadata->get('default_action', '__invoke'), '()');

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
