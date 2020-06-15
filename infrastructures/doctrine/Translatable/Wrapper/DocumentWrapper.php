<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable\Wrapper;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use ProxyManager\Proxy\GhostObjectInterface;
use Teknoo\East\Website\Object\TranslatableInterface;

class DocumentWrapper implements WrapperInterface
{
    private ?string $identifier = null;

    private ClassMetadata $meta;

    private TranslatableInterface $object;

    public function __construct(TranslatableInterface $object, DocumentManager $om)
    {
        $this->object = $object;
        $this->meta = $om->getClassMetadata(\get_class($this->object));
    }

    private function initialize(): void
    {
        if ($this->object instanceof GhostObjectInterface && !$this->object->isProxyInitialized()) {
            $this->object->initializeProxy();
        }
    }

    public function getObject(): TranslatableInterface
    {
        return $this->object;
    }

    public function getPropertyValue(string $name)
    {
        $this->initialize();

        $propertyReflection = $this->meta->getReflectionProperty($name);
        $propertyReflection->setAccessible(true);

        return $propertyReflection->getValue($this->object);
    }

    public function setPropertyValue(string $name, $value): self
    {
        $this->initialize();

        $propertyReflection = $this->meta->getReflectionProperty($name);
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->object, $value);

        return $this;
    }

    public function getIdentifier(): ?string
    {
        if ($this->identifier) {
            return $this->identifier;
        }

        if ($this->object instanceof GhostObjectInterface && !$this->object->isProxyInitialized()) {
            $this->object->initializeProxy();
        }

        $this->identifier = (string) $this->getPropertyValue($this->meta->identifier);

        return $this->identifier;
    }
}
