<?php

/*
 * East Common.
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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Recipe\Step;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\View\ParametersBag;

/**
 * Recipe's step, used in the main East Foundation 's recipe, as middleware, to initialize the Parameters bags and
 * register it into the manager's workplan. It must be used (as step's ingredient) to pass some value to the view.
 *
 * @see ParametersBag
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class InitParametersBag
{
    public function __invoke(ManagerInterface $manager): self
    {
        $manager->updateWorkPlan([
            ParametersBag::class => new ParametersBag()
        ]);

        return $this;
    }
}
