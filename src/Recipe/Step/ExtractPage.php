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

namespace Teknoo\East\Website\Recipe\Step;

use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * Recipe step to extract from server request the required page (from the key `page`) and put it in the
 * manager's workplan at `page` after int conversion.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ExtractPage
{
    public function __invoke(ManagerInterface $manager, string $page = '1'): self
    {
        $page = (int) $page;

        if ($page < 1) {
            $page = 1;
        }

        $manager->updateWorkPlan(['page' => $page]);

        return $this;
    }
}
