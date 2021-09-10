<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Service;

use Teknoo\Recipe\Promise\Promise;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Object\DeletableInterface;
use Teknoo\East\Website\Object\SluggableInterface;
use Teknoo\East\Website\Query\FindBySlugQuery;

use function array_map;
use function implode;
use function preg_replace;
use function strtolower;
use function trim;

/**
 * Service to find a uniq slug about an object, in its class domain, thanks to its dedicated loader, and loop until it
 * found a non used slug and update the object with a valid slug.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class FindSlugService
{
    private function sluggify(string $text): string
    {
        return strtolower(trim((string) preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
    }

    /**
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

            $candidate = implode($glue, array_map([$this, 'sluggify'], $candidateParts));

            $loader->query(
                new FindBySlugQuery($slugField, $candidate, $sluggable instanceof DeletableInterface),
                new Promise(
                    function () use (&$counter) {
                        $counter++;
                    },
                    function () use ($sluggable, $candidate, &$candidateAccepted) {
                        $sluggable->setSlug($candidate);
                        $candidateAccepted = true;
                    }
                )
            );
        } while (false === $candidateAccepted);

        return $this;
    }
}
