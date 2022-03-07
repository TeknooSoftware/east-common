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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Recipe\Step;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;

use function array_pop;
use function explode;
use function trim;

/**
 * Recipe step to extract from server request the required page (from the key `slug`) and put it in the
 * manager's workplan at `slug`.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ExtractSlug
{
    /**
     * @return array<int, string>
     */
    private function parseUrl(ServerRequestInterface $request): array
    {
        return explode('/', trim((string) $request->getUri()->getPath(), '/'));
    }

    public function __invoke(ServerRequestInterface $request, ManagerInterface $manager): self
    {
        $urlParts = $this->parseUrl($request);
        $slug = array_pop($urlParts);

        if (empty($slug)) {
            $slug = 'default';
        }

        $manager->updateWorkPlan(['slug' => $slug]);

        return $this;
    }
}
