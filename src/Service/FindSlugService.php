<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Service;

use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\DeletableInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\SluggableInterface;
use Teknoo\East\Common\Query\FindBySlugQuery;
use Teknoo\Recipe\Promise\Promise;

use function array_map;
use function implode;
use function preg_replace;
use function strtolower;
use function trim;

/**
 * Service to find a uniq slug about an object, in its class domain, thanks to its dedicated loader, and loop until it
 * found a non used slug and update the object with a valid slug.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class FindSlugService
{
    private function sluggify(string $text): string
    {
        return strtolower(trim((string) preg_replace('#[^A-Za-z0-9-]+#', '-', $text)));
    }

    /**
     * @param LoaderInterface<SluggableInterface<IdentifiedObjectInterface>> $loader
     * @param SluggableInterface<IdentifiedObjectInterface> $sluggable
     * @param array<string|int, mixed> $parts
     */
    public function process(
        LoaderInterface $loader,
        string $slugField,
        SluggableInterface $sluggable,
        array $parts,
        string $glue = '-'
    ): self {
        $counter = 1;
        $candidateAccepted = false;
        do {
            $candidateParts = $parts;
            if ($counter > 1) {
                $candidateParts[] = $counter;
            }

            $candidate = implode($glue, array_map($this->sluggify(...), $candidateParts));

            $object = null;
            if ($sluggable instanceof IdentifiedObjectInterface) {
                $object = $sluggable;
            }

            /** @var Promise<SluggableInterface<IdentifiedObjectInterface>, mixed, mixed> $sluggableFetchedPromise */
            $sluggableFetchedPromise = new Promise(
                static function () use (&$counter): void {
                    ++$counter;
                },
                static function () use ($sluggable, $candidate, &$candidateAccepted): void {
                    $sluggable->setSlug($candidate);
                    $candidateAccepted = true;
                }
            );

            $loader->fetch(
                new FindBySlugQuery(
                    $slugField,
                    $candidate,
                    $sluggable instanceof DeletableInterface,
                    $object
                ),
                $sluggableFetchedPromise
            );
        } while (false === $candidateAccepted);

        return $this;
    }
}
