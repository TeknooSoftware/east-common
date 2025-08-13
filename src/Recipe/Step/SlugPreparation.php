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

namespace Teknoo\East\Common\Recipe\Step;

use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Object\SluggableInterface;
use Teknoo\East\Common\Service\FindSlugService;

/**
 * Recipe step to prepare a persisted and sluggable object to generate a new uniq slug (if needed) and inject it into
 * the object before save in a next step.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SlugPreparation
{
    public function __construct(
        private readonly FindSlugService $findSlugService,
    ) {
    }

    /**
     * @param LoaderInterface<IdentifiedObjectInterface&SluggableInterface<IdentifiedObjectInterface>> $loader
     */
    public function __invoke(LoaderInterface $loader, ObjectInterface $object, ?string $slugField = null): self
    {
        if (!$object instanceof SluggableInterface || null === $slugField) {
            return $this;
        }

        /** @var IdentifiedObjectInterface&SluggableInterface<IdentifiedObjectInterface> $object */
        $object->prepareSlugNear(
            $loader,
            $this->findSlugService,
            $slugField,
        );

        return $this;
    }
}
