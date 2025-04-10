<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Recipe\Step;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;

use function array_pop;
use function explode;
use function trim;

/**
 * Recipe step to extract from server request the required page (from the key `slug`) and put it in the
 * manager's workplan at `slug`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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
